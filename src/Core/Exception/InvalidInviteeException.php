<?php

namespace App\Core\Exception;

use App\Core\Entity\User\User;

/**
 * Thrown when trying to add an invalid invitee to an announcement or a group.
 *
 * @author Dahiorus
 */
final class InvalidInviteeException extends InvalidParameterException
{

    /**
     * @var User
     */
    private $invitee;


    /**
     * InvalidRecipientException constructor.
     *
     * @param User $recipient The recipient of the entity
     * @param string $message [optional] The exception message
     */
    public function __construct(User $recipient, string $message = "Invalid invitee")
    {
        parent::__construct("invitee", $message);

        $this->invitee = $recipient;
    }


    /**
     * @return User
     */
    public function getInvitee() : User
    {
        return $this->invitee;
    }


    public function getDetails() : array
    {
        $array = parent::getDetails();
        $array["errors"]["invitee"] = $this->invitee->getUsername();

        return $array;
    }
}