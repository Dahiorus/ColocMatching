<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

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
     * @var string
     *
     * @SWG\Property(description="User type")
     */
    private $type;

    /**
     * @var ProfileFilter
     *
     * @SWG\Property(ref="#/definitions/ProfileFilter", description="Profile filter")
     */
    private $profileFilter;


    public function __toString(): string {
        return sprintf("UserFilter[%s] [type: '%s', profileFilter: %s]", parent::__toString(), $this->type,
            $this->profileFilter);
    }


    public function getType() {
        return $this->type;
    }


    public function setType(?string $type) {
        $this->type = $type;
        return $this;
    }


    public function getProfileFilter() {
        return $this->profileFilter;
    }


    public function setProfileFilter(ProfileFilter $profileFilter = null) {
        $this->profileFilter = $profileFilter;
        return $this;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Repository\Filter\AbstractFilter::buildCriteria()
     */
    public function buildCriteria(): Criteria {
        /** @var Criteria */
        $criteria = Criteria::create();

        if (!empty($this->type)) {
            $criteria->andWhere($criteria->expr()->eq("type", $this->type));
        }

        return $criteria;
    }

}