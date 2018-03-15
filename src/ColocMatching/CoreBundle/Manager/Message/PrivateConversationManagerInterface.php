<?php

namespace ColocMatching\CoreBundle\Manager\Message;

use ColocMatching\CoreBundle\Entity\User\PrivateConversation;
use ColocMatching\CoreBundle\Entity\User\PrivateMessage;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidRecipientException;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;

interface PrivateConversationManagerInterface
{
    /**
     * Lists all private conversations of a user
     *
     * @param User $participant The participant of the conversations
     * @param PageableFilter $filter The pagination filter
     *
     * @return PrivateConversation[]
     */
    public function findAll(User $participant, PageableFilter $filter) : array;


    /**
     * Counts all private conversations of a user
     *
     * @param User $participant The participant of the conversations
     *
     * @return int
     */
    public function countAll(User $participant) : int;


    /**
     * Finds one conversation between 2 users, can return null
     *
     * @param User $first The first participant
     * @param User $second The second participant
     *
     * @return PrivateConversation|null
     */
    public function findOne(User $first, User $second);


    /**
     * Lists the messages between 2 users with pagination
     *
     * @param User $first The first participant
     * @param User $second The second participant
     * @param PageableFilter $filter The pagination filter
     *
     * @return PrivateMessage[]
     */
    public function listMessages(User $first, User $second, PageableFilter $filter) : array;


    /**
     * Counts the messages between 2 users
     *
     * @param User $first The first participant
     * @param User $second The second participant
     *
     * @return int
     */
    public function countMessages(User $first, User $second) : int;


    /**
     * Posts a new message between 2 users
     *
     * @param User $author The author of the message
     * @param User $recipient The recipient of the message
     * @param array $data The data of the message
     *
     * @return PrivateMessage
     * @throws InvalidRecipientException
     * @throws InvalidFormException
     */
    public function createMessage(User $author, User $recipient, array $data) : PrivateMessage;

}
