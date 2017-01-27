<?php

namespace ColocMatching\CoreBundle\Entity\User;

/**
 * Description of UserConstants
 *
 * @author Utilisateur
 */
interface UserConstants {

    /* roles */
    const ROLE_DEFAULT = "ROLE_USER";

    /* gender */
    const GENDER_MALE = "male";

    const GENDER_FEMALE = "female";

    const GENDER_UNKNOWN = "unknown";

    /* type */
    const TYPE_SEARCH = "search";

    const TYPE_PROPOSAL = "proposal";

}
