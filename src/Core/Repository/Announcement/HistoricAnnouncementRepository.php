<?php

namespace App\Core\Repository\Announcement;

use App\Core\Entity\Announcement\HistoricAnnouncement;
use App\Core\Entity\User\User;
use App\Core\Repository\EntityRepository;
use App\Core\Repository\Filter\HistoricAnnouncementFilter;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\QueryBuilder;

class HistoricAnnouncementRepository extends EntityRepository
{
    protected const ALIAS = "ha";
    private const CREATOR_ALIAS = "c";


    /**
     * Finds a user's historic announcements with paging
     *
     * @param User $creator The historic announcements creator
     * @param Pageable|null $pageable Paging information
     *
     * @return HistoricAnnouncement[]
     */
    public function findByCreator(User $creator, Pageable $pageable = null) : array
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        $this->joinCreatorId($qb, $creator->getId());

        if (!empty($pageable))
        {
            $this->setPaging($qb, $pageable);
        }

        return $qb->getQuery()->getResult();
    }


    /**
     * Creates a query builder with the filter
     *
     * @param HistoricAnnouncementFilter $filter
     *
     * @return QueryBuilder
     * @throws \Doctrine\ORM\Query\QueryException
     */
    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getCreatorId()))
        {
            $this->joinCreatorId($queryBuilder, $filter->getCreatorId());
        }

        return $queryBuilder;
    }


    private function joinCreatorId(QueryBuilder $queryBuilder, int $creatorId)
    {
        $queryBuilder->join(self::ALIAS . ".creator", self::CREATOR_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::CREATOR_ALIAS . ".id", ":creatorId"));
        $queryBuilder->setParameter("creatorId", $creatorId);
    }

}