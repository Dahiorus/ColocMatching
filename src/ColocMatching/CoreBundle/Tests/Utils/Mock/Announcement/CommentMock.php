<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Comment;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CommentMock {

    public static function createComment(int $id, User $author, ?string $message, int $rate = 0) : Comment {
        $comment = new Comment($author);

        $comment->setId($id);
        $comment->setMessage($message);
        $comment->setRate($rate);

        return $comment;
    }


    public static function createComments(int $total) : Collection {
        $comments = array ();

        for ($id = 1; $id <= $total; $id++) {
            $userId = rand(1, 20);

            $comments[] = self::createComment($id,
                UserMock::createUser($userId, "user-$userId@test.fr", "password", "User-$userId",
                    "Test", UserConstants::TYPE_SEARCH), "Comment message", rand(0, 5));
        }

        return new ArrayCollection($comments);
    }
}
