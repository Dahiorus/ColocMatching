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
     * @var boolean
     *
     * @SWG\Property(description="User is enabled")
     */
    private $enabled;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="User creation date since", format="date")
     */
    private $createdAtSince;

    /**
     * @var \DateTime
     *
     * @SWG\Property(description="User creation date until", format="date")
     */
    private $createdAtUntil;

    /**
     * @var ProfileFilter
     *
     * @SWG\Property(ref="#/definitions/ProfileFilter", description="Profile filter")
     */
    private $profileFilter;


    public function __toString(): string {
        $createdAtSince = empty($this->createdAtSince) ? "" : $this->createdAtSince->format(\DateTime::ISO8601);
        $createdAtUntil = empty($this->createdAtUntil) ? "" : $this->createdAtUntil->format(\DateTime::ISO8601);

        return sprintf(
            "UserFilter[%s] [type: '%s', enabled: %b, createdAtSince: '%s', createdAtUntil: '%s', profileFilter: %s]",
            parent::__toString(), $this->type, $this->enabled, $createdAtSince, $createdAtUntil, $this->profileFilter);
    }


    public function getType() {
        return $this->type;
    }


    public function setType(?string $type) {
        $this->type = $type;
        return $this;
    }


    public function getEnabled() {
        return $this->enabled;
    }


    public function setEnabled(?bool $enabled) {
        $this->enabled = $enabled;
        return $this;
    }


    public function getCreatedAtSince() {
        return $this->createdAtSince;
    }


    public function setCreatedAtSince(\DateTime $createdAtSince = null) {
        $this->createdAtSince = $createdAtSince;
        return $this;
    }


    public function getCreatedAtUntil() {
        return $this->createdAtUntil;
    }


    public function setCreatedAtUntil(\DateTime $createdAtUntil = null) {
        $this->createdAtUntil = $createdAtUntil;
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

        if (!empty($this->enabled)) {
            $criteria->andWhere($criteria->expr()->eq("enabled", $this->enabled));
        }

        if (!empty($this->createdAtSince)) {
            $criteria->andWhere($criteria->expr()->gte("createdAt", $this->createdAtSince));
        }

        if (!empty($this->createdAtUntil)) {
            $criteria->andWhere($criteria->expr()->lte("createdAt", $this->createdAtUntil));
        }

        return $criteria;
    }

}