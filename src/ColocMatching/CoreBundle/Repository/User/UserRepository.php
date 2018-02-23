<?php

namespace ColocMatching\CoreBundle\Repository\User;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\ProfileFilter;
use ColocMatching\CoreBundle\Repository\Filter\UserFilter;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    protected const ALIAS = "u";
    private const ANNOUNCEMENT_ALIAS = "a";
    private const GROUP_ALIAS = "g";
    private const PROFILE_ALIAS = "p";


    /**
     * Creates a query builder from the filter
     *
     * @param UserFilter $filter
     *
     * @return QueryBuilder
     * @throws ORMException
     */
    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getProfileFilter()))
        {
            $this->joinProfile($queryBuilder, $filter->getProfileFilter());
        }

        if ($filter->hasAnnouncement())
        {
            $this->hasAnnouncementOnly($queryBuilder);
        }

        if ($filter->hasGroup())
        {
            $this->hasGroupOnly($queryBuilder);
        }

        return $queryBuilder;
    }


    private function joinProfile(QueryBuilder $queryBuilder, ProfileFilter $profileFilter)
    {
        /** @var string */
        $profileAlias = self::PROFILE_ALIAS;

        $queryBuilder->join(self::ALIAS . ".profile", $profileAlias);

        if (!empty($profileFilter->getGender()) && $profileFilter->getGender())
        {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("$profileAlias.gender", ":gender"));
            $queryBuilder->setParameter("gender", $profileFilter->getGender(), Type::STRING);
        }

        if (!empty($profileFilter->getAgeStart()))
        {
            $ageStart = $profileFilter->getAgeStart();

            $queryBuilder->andWhere($queryBuilder->expr()->lte("$profileAlias.birthDate", ":ageStart"));
            $queryBuilder->setParameter("ageStart", new \DateTime("-$ageStart years"), Type::DATE);
        }

        if (!empty($profileFilter->getAgeEnd()))
        {
            $ageEnd = $profileFilter->getAgeEnd();

            $queryBuilder->andWhere($queryBuilder->expr()->gte("$profileAlias.birthDate", ":ageEnd"));
            $queryBuilder->setParameter("ageEnd", new \DateTime("-$ageEnd years"), Type::DATE);
        }

        if ($profileFilter->getWithDescription())
        {
            $queryBuilder->andWhere($queryBuilder->expr()->isNotNull("$profileAlias.description"));
        }

        if (!is_null($profileFilter->isSmoker()))
        {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("$profileAlias.smoker", ":smoker"));
            $queryBuilder->setParameter("smoker", $profileFilter->isSmoker(), Type::BOOLEAN);
        }

        if (!is_null($profileFilter->hasJob()))
        {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("$profileAlias.hasJob", ":hasJob"));
            $queryBuilder->setParameter("hasJob", $profileFilter->hasJob(), Type::BOOLEAN);
        }

        if (!empty($profileFilter->getDiet()))
        {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("$profileAlias.diet", ":diet"));
            $queryBuilder->setParameter("diet", $profileFilter->getDiet(), Type::STRING);
        }

        if (!empty($profileFilter->getMaritalStatus()))
        {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("$profileAlias.maritalStatus", ":maritalStatus"));
            $queryBuilder->setParameter("maritalStatus", $profileFilter->getMaritalStatus(), Type::STRING);
        }

        if (!empty($profileFilter->getSocialStatus()))
        {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("$profileAlias.socialStatus", ":socialStatus"));
            $queryBuilder->setParameter("socialStatus", $profileFilter->getSocialStatus(), Type::STRING);
        }
    }


    private function hasAnnouncementOnly(QueryBuilder $queryBuilder)
    {
        /** @var string */
        $announcementAlias = self::ANNOUNCEMENT_ALIAS;

        $queryBuilder->andWhere(
            $queryBuilder->expr()->exists(
                sprintf(
                    "SELECT $announcementAlias.id FROM %s $announcementAlias WHERE $announcementAlias.creator = %s",
                    Announcement::class, self::ALIAS)));
    }


    private function hasGroupOnly(QueryBuilder $queryBuilder)
    {
        /** @var string */
        $groupAlias = self::GROUP_ALIAS;

        $queryBuilder->andWhere(
            $queryBuilder->expr()->exists(
                sprintf("SELECT $groupAlias.id FROM %s $groupAlias WHERE $groupAlias.creator = %s", Group::class,
                    self::ALIAS)));
    }

}
