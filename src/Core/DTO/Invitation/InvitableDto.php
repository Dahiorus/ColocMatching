<?php

namespace App\Core\DTO\Invitation;

use App\Core\DTO\DtoInterface;

/**
 * Interface to implement to use with the Invitations
 *
 * @author Dahiorus
 */
interface InvitableDto extends DtoInterface
{
    /**
     * Gets the invitable creator identifier
     * @return int
     */
    public function getCreatorId();
}