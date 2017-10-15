<?php

namespace ColocMatching\CoreBundle\Manager\Message;

use ColocMatching\CoreBundle\Entity\Message\Message;
use ColocMatching\CoreBundle\Entity\User\PrivateConversation;
use ColocMatching\CoreBundle\Entity\User\PrivateMessage;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidRecipientException;
use ColocMatching\CoreBundle\Form\Type\Message\MessageType;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Message\PrivateConversationRepository;
use ColocMatching\CoreBundle\Validator\EntityValidator;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * Entity manager for the entities PrivateConversation and PrivateMessage
 *
 * @author Dahiorus
 */
class PrivateConversationManager implements PrivateConversationManagerInterface {

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var PrivateConversationRepository
     */
    private $repository;

    /**
     * @var EntityValidator
     */
    private $entityValidator;


    public function __construct(ObjectManager $manager, string $entityClass, EntityValidator $entityValidator,
        LoggerInterface $logger) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->entityValidator = $entityValidator;
        $this->logger = $logger;
    }


    public function findAll(User $participant, PageableFilter $filter) : array {
        $this->logger->debug("Finding all conversations of a user with pagination",
            array ("participant" => $participant, "filter" => $filter));

        return $this->repository->findByParticipant($filter, $participant);
    }


    public function countAll(User $participant) : int {
        $this->logger->debug("Counting all conversations of a user", array ("participant" => $participant));

        return $this->repository->countByParticipant($participant);
    }


    public function findOne(User $first, User $second) {
        $this->logger->debug("Finding one conversation between 2 users",
            array ("first" => $first, "second" => $second));

        return $this->repository->findOneByParticipants($first, $second);
    }


    public function listMessages(User $first, User $second, PageableFilter $filter) : array {
        $this->logger->debug("Listing messages between 2 users",
            array ("first" => $first, "second" => $second, "filter" => $filter));

        /** @var PrivateConversation $conversation */
        $conversation = $this->findOne($first, $second);

        if (empty($conversation)) {
            return array ();
        }

        /** @var array<PrivateMessage> $messages */
        $messages = $conversation->getMessages()->toArray();
        $offset = $filter->getOffset();
        $size = $filter->getSize();

        return array_slice($messages, $offset, $size);
    }


    public function createMessage(User $author, User $recipient, array $data) : PrivateConversation {
        $this->logger->debug("Posting a new private message",
            array ("author" => $author, "recipient" => $recipient, "data" => $data));

        if ($author === $recipient) {
            throw new InvalidRecipientException($recipient, "Cannot send a message to yourself");
        }

        /** @var PrivateMessage $message */
        $message = $this->entityValidator->validateEntityForm(new PrivateMessage($author, $recipient), $data,
            MessageType::class, true);
        /** @var PrivateConversation $conversation */
        $conversation = $this->findOne($author, $recipient);

        if (empty($conversation)) {
            $this->logger->debug("Creating a new conversation between 2 users",
                array ("first" => $author, "second" => $recipient));

            $conversation = new PrivateConversation($author, $recipient);
        }
        else {
            $this->logger->debug("Posting a new message in an existing conversation",
                array ("conversation" => $conversation));

            /** @var Message $parent */
            $parent = $conversation->getMessages()->last();
            $message->setParent($parent ?: null);
        }

        $conversation->addMessage($message);

        $this->manager->persist($conversation);
        $this->manager->flush();

        return $conversation;
    }

}