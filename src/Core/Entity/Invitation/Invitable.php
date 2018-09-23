<?php

namespace App\Core\Entity\Invitation;

use App\Core\Entity\EntityInterface;
use App\Core\Entity\User\User;
use Doctrine\Common\Collections\Collection;

/**
 * An entity which implements this interface can be the source of an invitation
 *
 * @author Dahiorus
 */
interface Invitable extends EntityInterface
{

    public function getCreator() : User;


    public function getInvitees() : Collection;


    public function setInvitees(Collection $invitees = null);


    public function addInvitee(User $invitee = null);


    public function removeInvitee(User $invitee = null);


    public function hasInvitee(User $invitee) : bool;


    public function isAvailable() : bool;
}