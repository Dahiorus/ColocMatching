<?php

namespace ColocMatching\CoreBundle\Entity\User;

/**
 * Constants of class User
 *
 * @author Dahiorus
 */
final class UserConstants
{
    /* roles */
    const ROLE_DEFAULT = "ROLE_USER";
    const ROLE_SEARCH = "ROLE_SEARCH";
    const ROLE_PROPOSAL = "ROLE_PROPOSAL";

    /* type */
    const TYPE_SEARCH = "search";
    const TYPE_PROPOSAL = "proposal";

    /* status */
    const STATUS_PENDING = "pending";
    const STATUS_ENABLED = "enabled";
    const STATUS_VACATION = "vacation";
    const STATUS_BANNED = "banned";

}
