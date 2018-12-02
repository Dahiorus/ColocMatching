<?php

namespace App\Rest\Event;

use App\Core\DTO\Invitation\InvitationDto;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event triggered after an invitation is answered
 *
 * @author Dahiorus
 */
class InvitationAnsweredEvent extends Event
{
    /**
     * @var InvitationDto
     */
    private $invitation;


    public function __construct(InvitationDto $invitation)
    {
        $this->invitation = $invitation;
    }


    public function __toString()
    {
        return "InvitationAnsweredEvent [invitation = " . $this->invitation . "]";
    }


    public function getInvitation() : InvitationDto
    {
        return $this->invitation;
    }
}