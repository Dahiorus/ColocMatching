<?php

namespace ColocMatching\CoreBundle\Manager\Message;

use ColocMatching\CoreBundle\DTO\Message\PrivateConversationDto;
use ColocMatching\CoreBundle\DTO\Message\PrivateMessageDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidRecipientException;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\ORMException;

interface PrivateConversationDtoManagerInterface
{
    /**
     * Lists all private conversations of a user
     *
     * @param UserDto $participant The participant of the conversations
     * @param Pageable $pageable [optional] The pagination filter
     *
     * @return PrivateConversationDto[]
     */
    public function findAll(UserDto $participant, Pageable $pageable = null) : array;


    /**
     * Counts all private conversations of a user
     *
     * @param UserDto $participant The participant of the conversations
     *
     * @return int
     * @throws ORMException
     */
    public function countAll(UserDto $participant) : int;


    /**
     * Finds one conversation between 2 users, can return null
     *
     * @param UserDto $first The first participant
     * @param UserDto $second The second participant
     *
     * @return PrivateConversationDto|null
     * @throws ORMException
     */
    public function findOne(UserDto $first, UserDto $second);


    /**
     * Lists the messages between 2 users with pagination
     *
     * @param UserDto $first The first participant
     * @param UserDto $second The second participant
     * @param Pageable $pageable The pagination filter
     *
     * @return PrivateMessageDto[]
     * @throws ORMException
     */
    public function listMessages(UserDto $first, UserDto $second, Pageable $pageable = null) : array;


    /**
     * Counts the messages between 2 users
     *
     * @param UserDto $first The first participant
     * @param UserDto $second The second participant
     *
     * @return int
     * @throws ORMException
     */
    public function countMessages(UserDto $first, UserDto $second) : int;


    /**
     * Posts a new message between 2 users
     *
     * @param UserDto $author The author of the message
     * @param UserDto $recipient The recipient of the message
     * @param array $data The data of the message
     * @param bool $flush If the operation must be flushed
     *
     * @return PrivateMessageDto
     * @throws InvalidRecipientException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function createMessage(UserDto $author, UserDto $recipient, array $data,
        bool $flush = true) : PrivateMessageDto;


    /**
     * Deletes a PrivateConversationDto
     *
     * @param PrivateConversationDto $dto The entity to delete
     * @param bool $flush If the operation must be flushed
     */
    public function delete(PrivateConversationDto $dto, bool $flush = true) : void;


    /**
     * Deletes all PrivateConversationDto
     *
     * @param bool $flush If the operation must be flushed
     */
    public function deleteAll(bool $flush = true) : void;
}