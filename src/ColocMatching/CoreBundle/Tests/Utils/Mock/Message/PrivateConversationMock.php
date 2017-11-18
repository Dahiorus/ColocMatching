<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Message;

use ColocMatching\CoreBundle\Entity\User\PrivateConversation;
use ColocMatching\CoreBundle\Entity\User\PrivateMessage;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;

class PrivateConversationMock {

    public static function create(int $id, User $first, User $second) : PrivateConversation {
        $conversation = new PrivateConversation($first, $second);

        $conversation->setId($id);

        return $conversation;
    }


    public static function createPage(User $participant, PageableFilter $filter, int $total) : array {
        $conversations = array ();

        for ($id = 1; $id <= $total; $id++) {
            $secondParticipant = UserMock::createUser($id, "user-test-$id@test.fr", "password", "User $id", "Test",
                UserConstants::TYPE_SEARCH);
            $conversations[] = self::create($id, $participant, $secondParticipant);
        }

        return array_slice($conversations, $filter->getOffset(), $filter->getSize());
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