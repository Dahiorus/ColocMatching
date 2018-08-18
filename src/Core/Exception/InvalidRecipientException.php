<?php

namespace App\Core\Exception;

use App\Core\Entity\User\User;

/**
 * Thrown when the recipient of the entity (invitation, message, etc.) is invalid (eg. the recipient is the
 * author)
 *
 * @author Dahiorus
 */
final class InvalidRecipientException extends InvalidParameterException
{

    /**
     * @var User
     */
    private $recipient;


    /**
     * InvalidRecipientException constructor.
     *
     * @param User $recipient The recipient of the entity
     * @param string $message [optional] The exception message
     */
    public function __construct(User $recipient, string $message = "Invalid recipient")
    {
        parent::__construct("recipient", $message);

        $this->recipient = $recipient;
    }


    /**
     * @return User
     */
    public function getRecipient() : User
    {
        return $this->recipient;
    }


    public function getDetails() : array
    {
        $details = parent::getDetails();
        $details["errors"]["recipient"] = $this->recipient->getUsername();

        return $details;
    }
}