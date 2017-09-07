<?php

namespace ColocMatching\CoreBundle\Repository\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\HousingFilter;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;

/**
 * AnnouncementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnnouncementRepository extends EntityRepository {

    protected const ALIAS = "a";
    private const LOCATION_ALIAS = "l";
    private const HOUSING_ALIAS = "h";
    private const PICTURE_ALIAS = "p";
    private const CANDIDATES_ALIAS = "c";


    public function findByFilter(AnnouncementFilter $filter, array $fields = null) : array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $this->setPagination($queryBuilder, $filter, self::ALIAS);

        if (!empty($fields)) {
            $queryBuilder->select($this->getReturnedFields(self::ALIAS, $fields));
        }

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByFilter(AnnouncementFilter $filter) : int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    public function findOneByCandidate(User $candidate) {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $this->joinCandidate($queryBuilder, $candidate);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }


    private function createFilterQueryBuilder(AnnouncementFilter $filter) : QueryBuilder {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getAddress())) {
            $this->joinAddress($queryBuilder, $filter->getAddress());
        }

        if (!empty($filter->getHousingFilter())) {
            $this->joinHousing($queryBuilder, $filter->getHousingFilter());
        }

        if ($filter->withPictures()) {
            $this->withPicturesOnly($queryBuilder);
        }

        return $queryBuilder;
    }


    private function joinAddress(QueryBuilder &$queryBuilder, Address $address) {
        $addressAlias = self::LOCATION_ALIAS;

        $queryBuilder->join(self::ALIAS . ".location", $addressAlias);

        if (!empty($address->getStreetNumber())) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("$addressAlias.streetNumber", ":streetNumber"));
            $queryBuilder->setParameter("streetNumber", $address->getStreetNumber(), Type::STRING);
        }

        if (!empty($address->getRoute())) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("$addressAlias.route", ":route"));
            $queryBuilder->setParameter("route", $address->getRoute(), Type::STRING);
        }

        if (!empty($address->getLocality())) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("$addressAlias.locality", ":locality"));
            $queryBuilder->setParameter("locality", $address->getLocality(), Type::STRING);
        }

        if (!empty($address->getCountry())) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("$addressAlias.country", ":country"));
            $queryBuilder->setParameter("country", $address->getCountry(), Type::STRING);
        }

        if (!empty($address->getZipCode())) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("$addressAlias.zipCode", ":zipCode"));
            $queryBuilder->setParameter("zipCode", $address->getZipCode());
        }
    }


    private function joinHousing(QueryBuilder &$queryBuilder, HousingFilter $housingFilter) {
        $queryBuilder->join(self::ALIAS . ".housing", self::HOUSING_ALIAS);

        if (!empty($housingFilter->getTypes())) {
            $queryBuilder->andWhere($queryBuilder->expr()->in("type", ":types"));
            $queryBuilder->setParameter("types", $housingFilter->getTypes(), Type::TARRAY);
        }

        if (!empty($housingFilter->getRoomCount())) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("roomCount", ":roomCount"));
            $queryBuilder->setParameter("roomCount", $housingFilter->getRoomCount(), Type::INTEGER);
        }

        if (!empty($housingFilter->getBedroomCount())) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("bedroomCount", ":bedroomCount"));
            $queryBuilder->setParameter("bedroomCount", $housingFilter->getBedroomCount(), Type::INTEGER);
        }

        if (!empty($housingFilter->getBathroomCount())) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("bathroomCount", ":bathroomCount"));
            $queryBuilder->setParameter("bathroomCount", $housingFilter->getBathroomCount(), Type::INTEGER);
        }

        if (!empty($housingFilter->getSurfaceAreaMin())) {
            $queryBuilder->andWhere($queryBuilder->expr()->gte("surfaceArea", ":surfaceAreaMin"));
            $queryBuilder->setParameter("surfaceAreaMin", $housingFilter->getSurfaceAreaMin(), Type::INTEGER);
        }

        if (!empty($housingFilter->getSurfaceAreaMax())) {
            $queryBuilder->andWhere($queryBuilder->expr()->lte("surfaceArea", ":surfaceAreaMax"));
            $queryBuilder->setParameter("surfaceAreaMax", $housingFilter->getSurfaceAreaMax(), Type::INTEGER);
        }

        if (!empty($housingFilter->getRoomMateCount())) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("roomMateCount", ":roomMateCount"));
            $queryBuilder->setParameter("roomMateCount", $housingFilter->getRoomMateCount(), Type::INTEGER);
        }
    }


    private function withPicturesOnly(QueryBuilder &$queryBuilder) {
        $pictureAlias = self::PICTURE_ALIAS;

        $queryBuilder->andWhere(
            $queryBuilder->expr()->exists(
                sprintf("SELECT $pictureAlias.id FROM %s $pictureAlias WHERE $pictureAlias.announcement = %s",
                    AnnouncementPicture::class, self::ALIAS)));
    }


    private function joinCandidate(QueryBuilder &$queryBuilder, User $candidate) {
        $queryBuilder->join(self::ALIAS . ".candidates", self::CANDIDATES_ALIAS);

        $queryBuilder->andWhere($queryBuilder->expr()->isMemberOf(":candidate", self::CANDIDATES_ALIAS));
        $queryBuilder->setParameter("candidate", $candidate);
    }

}
