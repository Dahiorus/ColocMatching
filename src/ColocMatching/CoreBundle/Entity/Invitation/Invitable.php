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

    function getInvitees() : Collection;


    function setInvitees(Collection $invitees = null);


    function addInvitee(User $invitee = null);


    function removeInvitee(User $invitee = null);


    function isAvailable() : bool;
}