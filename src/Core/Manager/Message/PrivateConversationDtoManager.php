<?php

namespace App\Core\Manager\Message;

use App\Core\DTO\AbstractDto;
use App\Core\DTO\Collection;
use App\Core\DTO\Message\PrivateConversationDto;
use App\Core\DTO\Message\PrivateMessageDto;
use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\AbstractEntity;
use App\Core\Entity\Message\PrivateConversation;
use App\Core\Entity\Message\PrivateMessage;
use App\Core\Entity\User\User;
use App\Core\Exception\InvalidRecipientException;
use App\Core\Form\Type\Message\MessageDtoForm;
use App\Core\Mapper\DtoMapperInterface;
use App\Core\Mapper\Message\PrivateConversationDtoMapper;
use App\Core\Mapper\Message\PrivateMessageDtoMapper;
use App\Core\Mapper\User\UserDtoMapper;
use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Repository\Message\PrivateConversationRepository;
use App\Core\Repository\User\UserRepository;
use App\Core\Validator\FormValidator;
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
    public function findAll(UserDto $participant, Pageable $pageable = null)
    {
        $this->logger->debug("Listing private conversations with a participant",
            array ("participant" => $participant, "pageable" => $pageable));

        /** @var User $userEntity */
        $userEntity = $this->userDtoMapper->toEntity($participant);

        return $this->buildDtoCollection(
            $this->repository->findByParticipant($userEntity, $pageable),
            $this->repository->countByParticipant($userEntity), $pageable,
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
    public function listMessages(UserDto $first, UserDto $second, Pageable $pageable = null)
    {
        $this->logger->debug("Listing messages between 2 participants",
            array ("first" => $first, "second" => $second, "pageable" => $pageable));

        $firstEntity = $this->userDtoMapper->toEntity($first);
        $secondEntity = $this->userDtoMapper->toEntity($second);
        $entity = $this->repository->findOneByParticipants($firstEntity, $secondEntity);

        if (empty($entity))
        {
            return empty($pageable) ? new Collection([], 0) : new Page($pageable, [], 0);
        }

        $messages = !empty($pageable) ?
            $entity->getMessages()->slice($pageable->getOffset(), $pageable->getSize())
            : $entity->getMessages()->toArray();

        return $this->buildDtoCollection($messages, $entity->getMessages()->count(), $pageable);
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
     * Calls the entity manager to flush the operations and clears all managed objects
     *
     * @param bool $flush If the operations must be flushed
     */
    protected function flush(bool $flush) : void
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
     * @param AbstractEntity[] $entities The entities
     * @param int $total The total listing count
     * @param Pageable $pageable [optional] Paging information
     * @param DtoMapperInterface $mapper [optional] The DTO mapper to use
     *
     * @return Collection|Page
     */
    private function buildDtoCollection(array $entities, int $total, Pageable $pageable = null,
        DtoMapperInterface $mapper = null)
    {
        /** @var AbstractDto[] $dto */
        $dto = $this->convertEntitiesToDtos($entities, $mapper);

        return empty($pageable) ? new Collection($dto, $total) : new Page($pageable, $dto, $total);
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
