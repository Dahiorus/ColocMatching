<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Entity\User\Profile;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use Doctrine\Common\Collections\Criteria;
use Swagger\Annotations as SWG;

/**
 * User query filter class
 *
 * @SWG\Definition(definition="UserFilter")
 * @author brondon.ung
 */
class UserFilter extends AbstractFilter {

    /**
     * @var Profile
     * @SWG\Property(ref="#/definitions/Profile", description="Profile filter")
     */
    private $profile;


    public function __toString(): string {
        return sprintf("UserFilter[%s] [profile: %s]", parent::__toString(), $this->profile);
    }


    public function getProfile() {
        return $this->profile;
    }


    public function setProfile(Profile $profile = null) {
        $this->profile = $profile;
        return $this;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Repository\Filter\AbstractFilter::buildCriteria()
     */
    public function buildCriteria(): Criteria {
        /** @var Criteria */
        $criteria = Criteria::create();

        return $criteria;
    }

}