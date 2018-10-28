<?php

namespace App\Core\Manager\Message;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\Message\GroupConversationDto;
use App\Core\DTO\Message\GroupMessageDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\EntityInterface;
use App\Core\Entity\Group\Group;
use App\Core\Entity\Message\GroupConversation;
use App\Core\Entity\User\User;
use App\Core\Exception\InvalidParameterException;
use App\Core\Form\Type\Message\MessageDtoForm;
use App\Core\Manager\Collection;
use App\Core\Manager\Page;
use App\Core\Mapper\DtoMapperInterface;
use App\Core\Mapper\Message\GroupConversationDtoMapper;
use App\Core\Mapper\Message\GroupMessageDtoMapper;
use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Repository\Group\GroupRepository;
use App\Core\Repository\Message\GroupConversationRepository;
use App\Core\Repository\User\UserRepository;
use App\Core\Validator\FormValidator;
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
    public function listMessages(GroupDto $group, Pageable $pageable = null)
    {
        $this->logger->debug("Listing a group messages", array ("group" => $group, "pageable" => $pageable));

        /** @var Group $groupEntity */
        $groupEntity = $this->groupRepository->find($group->getId());
        /** @var GroupConversation $entity */
        $entity = $this->repository->findOneByGroup($groupEntity);

        if (empty($entity))
        {
            return empty($pageable) ? new Collection([], 0) : new Page($pageable, [], 0);
        }

        $this->logger->debug("Conversation found", array ("conversation" => $entity));

        $messages = empty($pageable) ? $entity->getMessages()->toArray() :
            $entity->getMessages()->slice($pageable->getOffset(), $pageable->getSize());

        return $this->buildDtoCollection(
            $this->messageDtoMapper, $messages, $entity->getMessages()->count(), $pageable);
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


    /**
     * Builds a DTO Collection or a Page from the entities
     *
     * @param DtoMapperInterface $mapper The DTO mapper to use
     * @param EntityInterface[] $entities The entities
     * @param int $total The total listing count
     * @param Pageable $pageable [optional] Paging information
     *
     *
     * @return Collection|Page
     */
    private function buildDtoCollection(DtoMapperInterface $mapper, array $entities, int $total,
        Pageable $pageable = null)
    {
        /** @var AbstractDto[] $dtos */
        $dtos = array_map(function (EntityInterface $entity) use ($mapper) {
            return $mapper->toDto($entity);
        }, $entities);

        return empty($pageable) ? new Collection($dtos, $total) : new Page($pageable, $dtos, $total);
    }

}
