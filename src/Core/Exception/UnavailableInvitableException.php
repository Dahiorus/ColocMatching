<?php

namespace App\Core\Exception;

use App\Core\Entity\Invitation\Invitable;

/**
 * Thrown when an invitable is not available for an invitation
 */
class UnavailableInvitableException extends InvalidParameterException
{

    /**
     * @var Invitable
     */
    private $invitable;


    public function __construct(Invitable $invitable, string $message = "Unavailable invitable")
    {
        parent::__construct("invitable", $message);

        $this->invitable = $invitable;
    }


    /**
     * @return Invitable
     */
    public function getInvitable() : Invitable
    {
        return $this->invitable;
    }


    public function getDetails() : array
    {
        $array = parent::getDetails();
        $array["errors"]["invitable"] = $this->invitable;

        return $array;
    }
}