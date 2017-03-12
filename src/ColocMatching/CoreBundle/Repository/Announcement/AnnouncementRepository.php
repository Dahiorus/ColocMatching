<?php

namespace ColocMatching\CoreBundle\Repository\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;

/**
 * AnnouncementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnnouncementRepository extends EntityRepository {


    public function findByFilter(AnnouncementFilter $filter): array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter, "a");

        return $queryBuilder->getQuery()->getResult();
    }


    public function selectFieldsByFilter(AnnouncementFilter $filter, array $fields): array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter, "a");
        $queryBuilder->select($this->getReturnedFields("a", $fields));

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByFilter(AnnouncementFilter $filter): int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder("a");
        $queryBuilder->select($queryBuilder->expr()->countDistinct("a"));
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getAddress())) {
            $this->joinAddress($queryBuilder, $filter->getAddress(), "a", "l");
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    private function createFilterQueryBuilder(AnnouncementFilter $filter, string $alias = "a"): QueryBuilder {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder($alias);
        $queryBuilder->addCriteria($filter->buildCriteria());
        $this->setPagination($queryBuilder, $filter);
        $this->setOrderBy($queryBuilder, $filter, $alias);

        if (!empty($filter->getAddress())) {
            $this->joinAddress($queryBuilder, $filter->getAddress(), $alias, "l");
        }

        if (!empty($filter->getCreatorType())) {
            $this->joinCreatorType($queryBuilder, $filter->getCreatorType(), $alias, "c");
        }

        return $queryBuilder;
    }


    private function joinAddress(QueryBuilder &$queryBuilder, Address $address, string $alias = "a",
        string $addressAlias = "l") {
        $queryBuilder->join("$alias.location", $addressAlias);

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


    private function joinCreatorType(QueryBuilder $queryBuilder, string $creatorType, string $alias = "a",
        string $creatorAlias = "c") {
        $queryBuilder->join("$alias.creator", "$creatorAlias");
        $queryBuilder->andWhere($queryBuilder->expr()->eq("$creatorAlias.type", ":creatorType"));
        $queryBuilder->setParameter("creatorType", $creatorType, Type::STRING);
    }

}
