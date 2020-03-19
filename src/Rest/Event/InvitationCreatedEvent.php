<?php

namespace App\Rest\Event;

use App\Core\DTO\Invitation\InvitationDto;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event triggered after an invitation creation
 *
 * @author Dahiorus
 */
class InvitationCreatedEvent extends Event
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
        return "InvitationCreatedEvent [invitation = " . $this->invitation . "]";
    }


    public function getInvitation() : InvitationDto
    {
        return $this->invitation;
    }

}
