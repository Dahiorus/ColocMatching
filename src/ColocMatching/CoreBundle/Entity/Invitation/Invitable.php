<?php

namespace ColocMatching\CoreBundle\Entity\Invitation;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\Collections\Collection;

/**
 * An entity which implements this interface can be the source of an invitation
 *
 * @author Dahiorus
 */
interface Invitable extends EntityInterface {

    public function getCreator() : User;


    public function getInvitees() : Collection;


    public function setInvitees(Collection $invitees = null);


    public function addInvitee(User $invitee = null);


    public function removeInvitee(User $invitee = null);


    public function hasInvitee(User $invitee) : bool;


    public function isAvailable() : bool;
}