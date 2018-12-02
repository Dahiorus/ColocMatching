<?php

namespace App\Core\Manager\Message;

use App\Core\DTO\Collection;
use App\Core\DTO\Message\PrivateConversationDto;
use App\Core\DTO\Message\PrivateMessageDto;
use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidRecipientException;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\ORMException;

interface PrivateConversationDtoManagerInterface
{
    /**
     * Lists all private conversations of a user
     *
     * @param UserDto $participant The participant of the conversations
     * @param Pageable $pageable [optional] The pagination filter
     *
     * @return Collection|Page
     * @throws ORMException
     */
    public function findAll(UserDto $participant, Pageable $pageable = null);


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
     * @return Collection|Page
     * @throws ORMException
     */
    public function listMessages(UserDto $first, UserDto $second, Pageable $pageable = null);


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