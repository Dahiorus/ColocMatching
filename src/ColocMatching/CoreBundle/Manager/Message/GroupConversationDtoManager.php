<?php

namespace ColocMatching\CoreBundle\Manager\Message;

use ColocMatching\CoreBundle\DAO\GroupConversationDao;
use ColocMatching\CoreBundle\DAO\GroupDao;
use ColocMatching\CoreBundle\DAO\UserDao;
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
use ColocMatching\CoreBundle\Validator\FormValidator;
use Psr\Log\LoggerInterface;

class GroupConversationDtoManager implements GroupConversationDtoManagerInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var FormValidator */
    protected $formValidator;

    /** @var GroupConversationDao */
    private $conversationDao;

    /** @var GroupDao */
    private $groupDao;

    /** @var UserDao */
    private $userDao;

    /** @var GroupConversationDtoMapper */
    private $conversationDtoMapper;

    /** @var GroupMessageDtoMapper */
    private $messageDtoMapper;


    public function __construct(LoggerInterface $logger, FormValidator $formValidator,
        GroupConversationDao $conversationDao, GroupDao $groupDao, UserDao $userDao,
        GroupConversationDtoMapper $conversationDtoMapper, GroupMessageDtoMapper $messageDtoMapper)
    {
        $this->logger = $logger;
        $this->formValidator = $formValidator;
        $this->groupDao = $groupDao;
        $this->userDao = $userDao;
        $this->conversationDao = $conversationDao;
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
        $groupEntity = $this->groupDao->read($group->getId());
        $conversation = $this->conversationDtoMapper->toDto($this->conversationDao->findOneByGroup($groupEntity));

        if (empty($conversation))
        {
            return array ();
        }

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
        $groupEntity = $this->groupDao->read($group->getId());
        $conversation = $this->conversationDao->findOneByGroup($groupEntity);

        if (empty($conversation))
        {
            return 0;
        }

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
        $authorEntity = $this->userDao->read($author->getId());
        /** @var Group $groupEntity */
        $groupEntity = $this->groupDao->read($group->getId());

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
        $conversation = $this->conversationDao->findOneByGroup($groupEntity);

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

        $isNew ? $this->conversationDao->persist($conversation) : $this->conversationDao->merge($conversation);

        if ($flush)
        {
            $this->conversationDao->flush();
        }

        return $this->messageDtoMapper->toDto($messageEntity);
    }


    /**
     * @inheritdoc
     */
    public function delete(GroupConversationDto $dto, bool $flush = true) : void
    {
        $this->logger->debug("Deleting a group conversation", array ("conversation" => $dto, "flush" => $flush));

        /** @var GroupConversation $entity */
        $entity = $this->conversationDao->read($dto->getId());

        $this->conversationDao->delete($entity);

        if ($flush)
        {
            $this->conversationDao->flush();
        }
    }


    /**
     * @inheritdoc
     */
    public function deleteAll() : void
    {
        $this->logger->debug("Deleting all group conversations");

        /** @var GroupConversation[] $conversations */
        $conversations = $this->conversationDao->findAll();

        array_walk($conversations, function (GroupConversation $c) {
            $this->conversationDao->delete($c);
        });

        $this->conversationDao->flush();
    }

}