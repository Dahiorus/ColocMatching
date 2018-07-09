<?php

namespace ColocMatching\CoreBundle\Manager\Message;

use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\Message\GroupConversationDto;
use ColocMatching\CoreBundle\DTO\Message\GroupMessageDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Message\GroupConversation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Form\Type\Message\MessageDtoForm;
use ColocMatching\CoreBundle\Mapper\Message\GroupConversationDtoMapper;
use ColocMatching\CoreBundle\Mapper\Message\GroupMessageDtoMapper;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;
use ColocMatching\CoreBundle\Repository\Group\GroupRepository;
use ColocMatching\CoreBundle\Repository\Message\GroupConversationRepository;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\CoreBundle\Validator\FormValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class GroupConversationDtoManager implements GroupConversationDtoManagerInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var FormValidator */
    protected $formValidator;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var GroupConversationRepository */
    protected $repository;

    /** @var UserRepository */
    private $userRepository;

    /** @var GroupRepository */
    private $groupRepository;

    /** @var GroupConversationDtoMapper */
    private $conversationDtoMapper;

    /** @var GroupMessageDtoMapper */
    private $messageDtoMapper;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, FormValidator $formValidator,
        GroupConversationDtoMapper $conversationDtoMapper, GroupMessageDtoMapper $messageDtoMapper)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->repository = $em->getRepository(GroupConversation::class);
        $this->userRepository = $em->getRepository(User::class);
        $this->groupRepository = $em->getRepository(Group::class);
        $this->formValidator = $formValidator;
        $this->conversationDtoMapper = $conversationDtoMapper;
        $this->messageDtoMapper = $messageDtoMapper;
    }


    /**
     * @inheritdoc
     */
    public function listMessages(GroupDto $group, Pageable $pageable = null) : array
    {
        $this->logger->debug("Listing a group messages", array ("group" => $group, "pageable" => $pageable));

        /** @var Group $groupEntity */
        $groupEntity = $this->groupRepository->find($group->getId());
        $conversation = $this->conversationDtoMapper->toDto($this->repository->findOneByGroup($groupEntity));

        if (empty($conversation))
        {
            return array ();
        }

        $this->logger->debug("Conversation found", array ("conversation" => $conversation));

        if (!empty($pageable))
        {
            return $conversation->getMessages()->slice($pageable->getOffset(), $pageable->getSize());
        }

        return $conversation->getMessages()->toArray();
    }


    /**
     * @inheritdoc
     */
    public function countMessages(GroupDto $group) : int
    {
        $this->logger->debug("Counting a group messages", array ("group" => $group));

        /** @var Group $groupEntity */
        $groupEntity = $this->groupRepository->find($group->getId());
        $conversation = $this->repository->findOneByGroup($groupEntity);

        if (empty($conversation))
        {
            return 0;
        }

        $this->logger->debug("Conversation found", array ("conversation" => $conversation));

        return $conversation->getMessages()->count();
    }


    /**
     * @inheritdoc
     */
    public function createMessage(UserDto $author, GroupDto $group, array $data, bool $flush = true) : GroupMessageDto
    {
        $this->logger->debug("Posting a new group message",
            array ("author" => $author, "group" => $group, "data" => $data, "flush" => $flush));

        /** @var User $authorEntity */
        $authorEntity = $this->userRepository->find($author->getId());
        /** @var Group $groupEntity */
        $groupEntity = $this->groupRepository->find($group->getId());

        if (!$groupEntity->isAvailable())
        {
            throw new InvalidParameterException("group", "The group is not available");
        }

        if (!$groupEntity->hasInvitee($authorEntity))
        {
            throw new InvalidParameterException("author", "The author is not in the group");
        }

        // validate message data
        /** @var GroupMessageDto $message */
        $message = $this->formValidator->validateDtoForm(new GroupMessageDto(), $data, MessageDtoForm::class, true);
        $message->setGroupId($group->getId());
        $message->setAuthorId($author->getId());

        // find the group conversation -> if not exists create a new group conversation
        $conversation = $this->repository->findOneByGroup($groupEntity);

        if (empty($conversation))
        {
            $this->logger->debug("Creating a new group conversation", array ("group" => $group));

            $conversation = new GroupConversation($groupEntity);
            $isNew = true;
        }
        else
        {
            $this->logger->debug("Posting a new message to an existing group conversation",
                array ("conversation" => $conversation));

            $isNew = false;
        }

        // add the message to the conversation
        $messageEntity = $this->messageDtoMapper->toEntity($message);
        $conversation->addMessage($messageEntity);

        $isNew ? $this->em->persist($conversation) : $this->em->merge($conversation);
        $this->flush($flush);

        $this->logger->info("Message created", array ("message" => $messageEntity));

        return $this->messageDtoMapper->toDto($messageEntity);
    }


    /**
     * @inheritdoc
     */
    public function delete(GroupConversationDto $dto, bool $flush = true) : void
    {
        $this->logger->debug("Deleting a group conversation", array ("conversation" => $dto, "flush" => $flush));

        /** @var GroupConversation $entity */
        $entity = $this->repository->find($dto->getId());

        $this->em->remove($entity);
        $this->flush($flush);

        $this->logger->debug("Entity deleted",
            array ("domainClass" => GroupConversation::class, "id" => $dto->getId()));
    }


    /**
     * @inheritdoc
     */
    public function deleteAll() : void
    {
        $this->logger->debug("Deleting all group conversations");

        /** @var GroupConversation[] $conversations */
        $conversations = $this->repository->findAll();

        array_walk($conversations, function (GroupConversation $c) {
            $this->em->remove($c);
        });

        $this->flush(true);

        $this->logger->info("All entities deleted", array ("domainClass" => GroupConversation::class));
    }


    /**
     * Calls the entity manager to flush the operations and clears all managed objects
     *
     * @param bool $flush If the operations must be flushed
     */
    protected function flush(bool $flush)
    {
        if ($flush)
        {
            $this->logger->debug("Flushing operation");

            $this->em->flush();
            $this->em->clear();
        }
    }

}