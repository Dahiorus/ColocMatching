<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Message;

use ColocMatching\CoreBundle\Entity\User\PrivateConversation;
use ColocMatching\CoreBundle\Entity\User\PrivateMessage;
use ColocMatching\CoreBundle\Entity\User\User;

class PrivateConversationMock {

    public static function create(int $id, User $first, User $second) : PrivateConversation {
        $conversation = new PrivateConversation($first, $second);

        $conversation->setId($id);

        return $conversation;
    }


    public static function createMessage(int $id, User $author, PrivateConversation $conversation,
        string $content) : PrivateMessage {

        $recipient = ($author === $conversation->getFirstParticipant()) ? $conversation->getSecondParticipant()
            : $conversation->getFirstParticipant();
        $message = new PrivateMessage($author, $recipient);

        $message->setId($id);
        $message->setContent($content);

        /** @var PrivateMessage $parent */
        $parent = $conversation->getMessages()->last();
        $message->setParent($parent ?: null);

        $conversation->addMessage($message);

        return $message;
    }


    private function __construct() {
    }
}