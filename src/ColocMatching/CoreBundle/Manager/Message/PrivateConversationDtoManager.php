<?php

namespace ColocMatching\CoreBundle\Manager\Message;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\DTO\Message\PrivateConversationDto;
use ColocMatching\CoreBundle\DTO\Message\PrivateMessageDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\AbstractEntity;
use ColocMatching\CoreBundle\Entity\Message\PrivateConversation;
use ColocMatching\CoreBundle\Entity\Message\PrivateMessage;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidRecipientException;
use ColocMatching\CoreBundle\Form\Type\Message\MessageDtoForm;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
use ColocMatching\CoreBundle\Mapper\Message\PrivateConversationDtoMapper;
use ColocMatching\CoreBundle\Mapper\Message\PrivateMessageDtoMapper;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;
use ColocMatching\CoreBundle\Repository\Message\PrivateConversationRepository;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\CoreBundle\Validator\FormValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class PrivateConversationDtoManager implements PrivateConversationDtoManagerInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var PrivateConversationRepository */
    protected $repository;

    /** @var PrivateConversationDtoMapper */
    private $conversationDtoMapper;

    /** @var PrivateMessageDtoMapper */
    private $messageDtoMapper;

    /** @var FormValidator */
    private $formValidator;

    /** @var UserDtoMapper */
    private $userDtoMapper;

    /** @var UserRepository */
    private $userRepository;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em,
        PrivateConversationDtoMapper $conversationDtoMapper, PrivateMessageDtoMapper $messageDtoMapper,
        FormValidator $formValidator, UserDtoMapper $userDtoMapper)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->repository = $em->getRepository(PrivateConversation::class);
        $this->conversationDtoMapper = $conversationDtoMapper;
        $this->messageDtoMapper = $messageDtoMapper;
        $this->formValidator = $formValidator;
        $this->userDtoMapper = $userDtoMapper;
        $this->userRepository = $em->getRepository(User::class);
    }


    /**
     * @inheritdoc
     */
    public function findAll(UserDto $participant, Pageable $pageable = null) : array
    {
        $this->logger->debug("Listing private conversations with a participant",
            array ("participant" => $participant, "pageable" => $pageable));

        /** @var User $userEntity */
        $userEntity = $this->userDtoMapper->toEntity($participant);

        return $this->convertEntitiesToDtos($this->repository->findByParticipant($userEntity, $pageable),
            $this->conversationDtoMapper);
    }


    /**
     * @inheritdoc
     */
    public function countAll(UserDto $participant) : int
    {
        $this->logger->debug("Counting private conversations with a participant",
            array ("participant" => $participant));

        /** @var User $userEntity */
        $userEntity = $this->userDtoMapper->toEntity($participant);

        return $this->repository->countByParticipant($userEntity);
    }


    /**
     * @inheritdoc
     */
    public function findOne(UserDto $first, UserDto $second)
    {
        $this->logger->debug("Finding one conversation between 2 participants",
            array ("first" => $first, "second" => $second));

        $firstEntity = $this->userDtoMapper->toEntity($first);
        $secondEntity = $this->userDtoMapper->toEntity($second);
        $entity = $this->repository->findOneByParticipants($firstEntity, $secondEntity);

        if (!empty($entity))
        {
            $this->logger->info("Private conversation found", array ("conversation" => $entity));
        }

        return $this->conversationDtoMapper->toDto($entity);
    }


    /**
     * @inheritdoc
     */
    public function listMessages(UserDto $first, UserDto $second, Pageable $pageable = null) : array
    {
        $this->logger->debug("Listing messages between 2 participants",
            array ("first" => $first, "second" => $second, "pageable" => $pageable));

        /** @var PrivateConversationDto $conversation */
        $conversation = $this->findOne($first, $second);

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
    public function countMessages(UserDto $first, UserDto $second) : int
    {
        $this->logger->debug("Counting messages between 2 participants",
            array ("first" => $first, "second" => $second));

        /** @var PrivateConversationDto $conversation */
        $conversation = $this->findOne($first, $second);

        if (empty($conversation))
        {
            return 0;
        }

        return $conversation->getMessages()->count();
    }


    /**
     * @inheritdoc
     */
    public function createMessage(UserDto $author, UserDto $recipient, array $data,
        bool $flush = true) : PrivateMessageDto
    {
        $this->logger->debug("Posting a new private message",
            array ("author" => $author, "recipient" => $recipient, "data" => $data));

        /** @var User $recipientEntity */
        $recipientEntity = $this->userRepository->find($recipient->getId());
        /** @var User $authorEntity */
        $authorEntity = $this->userRepository->find($author->getId());

        if ($author->getId() == $recipient->getId())
        {
            throw new InvalidRecipientException($recipientEntity, "Cannot send a message to yourself");
        }

        if (!$recipientEntity->isEnabled())
        {
            throw new InvalidRecipientException($recipientEntity, "Cannot send a message to an invalid user");
        }

        /** @var PrivateMessageDto $messageDto */
        $messageDto = $this->formValidator->validateDtoForm(
            new PrivateMessageDto(), $data, MessageDtoForm::class, true);
        $messageDto->setRecipientId($recipient->getId());
        $messageDto->setAuthorId($author->getId());

        /** @var PrivateConversation $conversation */
        $conversation = $this->repository->findOneByParticipants($authorEntity, $recipientEntity);

        if (empty($conversation))
        {
            $this->logger->debug("Creating a new conversation between 2 users",
                array ("first" => $author, "second" => $recipient));

            $conversation = new PrivateConversation($authorEntity, $recipientEntity);
        }
        else
        {
            $conversationDto = $this->conversationDtoMapper->toDto($conversation);

            $this->logger->debug("Posting a new message to an existing conversation",
                array ("conversation" => $conversationDto));
        }

        /** @var PrivateMessage $message */
        $message = $this->messageDtoMapper->toEntity($messageDto);
        $conversation->addMessage($message);

        empty($conversation->getId()) ? $this->em->persist($conversation) : $this->em->merge($conversation);
        $this->flush($flush);

        $this->logger->info("Message created", array ("message" => $message));

        return $this->messageDtoMapper->toDto($message);
    }


    /**
     * @inheritdoc
     */
    public function delete(PrivateConversationDto $dto, bool $flush = true) : void
    {
        $this->logger->debug("Deleting a private conversation", array ("conversation" => $dto));

        /** @var PrivateConversation $entity */
        $entity = $this->repository->find($dto->getId());

        $this->em->remove($entity);
        $this->flush($flush);

        $this->logger->debug("Entity deleted",
            array ("domainClass" => PrivateConversation::class, "id" => $dto->getId()));
    }


    /**
     * @inheritdoc
     */
    public function deleteAll(bool $flush = true) : void
    {
        $this->logger->debug("Deleting all private conversations");

        /** @var PrivateConversation[] $entities */
        $entities = $this->repository->findAll();

        array_walk($entities, function (PrivateConversation $c) {
            $this->em->remove($c);
        });

        $this->flush($flush);

        $this->logger->info("All entities deleted", array ("domainClass" => PrivateMessage::class));
    }


    /**
     * Calls the entity manager to flush the operations
     *
     * @param bool $flush If the operations must be flushed
     */
    protected function flush(bool $flush) : void
    {
        if ($flush)
        {
            $this->logger->debug("Flushing operation");

            $this->em->flush();
        }
    }


    /**
     * Uses the DTO mapper to convert the entities to DTO
     *
     * @param AbstractEntity[] $entities The entities to convert
     * @param DtoMapperInterface $mapper The DTO mapper
     *
     * @return AbstractDto[]
     */
    private function convertEntitiesToDtos(array $entities, DtoMapperInterface $mapper)
    {
        return array_map(function (AbstractEntity $entity) use ($mapper) {
            return $mapper->toDto($entity);
        }, $entities);
    }

}