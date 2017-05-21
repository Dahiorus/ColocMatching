<?php

namespace ColocMatching\CoreBundle\Entity\User;

/**
 * Constants of class User
 *
 * @author brondon.ung
 */
interface UserConstants {

    /* roles */
    const ROLE_DEFAULT = "ROLE_USER";

    /* type */
    const TYPE_SEARCH = "search";

    const TYPE_PROPOSAL = "proposal";

    /* status */
    const STATUS_PENDING = "pending";

    const STATUS_ENABLED = "enabled";

    const STATUS_DISABLED = "disabled";

    const STATUS_BANNED = "banned";

}
