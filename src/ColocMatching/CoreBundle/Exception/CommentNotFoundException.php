<?php

namespace ColocMatching\CoreBundle\Exception;

use ColocMatching\CoreBundle\Entity\Announcement\Comment;

class CommentNotFoundException extends EntityNotFoundException {

    /**
     * Constructor
     *
     * @param string $name The name of the attribute on which the exception would be throw
     * @param mixed $value The value of the attribute
     * @param \Exception $previous
     * @param int $code
     */
    public function __construct(string $name, $value, \Exception $previous = null, $code = 0) {
        parent::__construct(Comment::class, $name, $value, $previous, $code);
    }
}