<?php

namespace ColocMatching\CoreBundle\Entity\User;

interface ProfileConstants {

    /* gender */
    const GENDER_MALE = "male";

    const GENDER_FEMALE = "female";

    const GENDER_UNKNOWN = "unknown";

    /* diet */
    const DIET_VEGETARIAN = "vegetarian";

    const DIET_VEGAN = "vegan";

    const DIET_MEAT_EATER = "meat_eater";

    const DIET_UNKNOWN = "unknown";

    /* marital status */
    const MARITAL_COUPLE = "couple";

    const MARITAL_SINGLE = "single";

    const MARITAL_UNKNOWN = "unknown";

    /* social status */
    const SOCIAL_STUDENT = "student";

    const SOCIAL_WORKER = "worker";

    const SOCIAL_UNKNOWN = "unknown";

}