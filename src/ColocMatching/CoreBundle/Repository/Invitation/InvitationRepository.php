<?php

namespace ColocMatching\CoreBundle\Repository\Invitation;

use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\QueryBuilder;

class InvitationRepository extends EntityRepository
{
    protected const ALIAS = "i";
    private const RECIPIENT_ALIAS = "r";


    /**
     * Finds invitations with a specific recipient and paging
     *
     * @param User $recipient The recipient
     * @param Pageable $pageable [optional] Paging information
     *
     * @return Invitation[]
     */
    public function findByRecipient(User $recipient, Pageable $pageable = null) : array
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->joinRecipientId($queryBuilder, $recipient->getId());

        if (!empty($pageable))
        {
            $this->setPaging($queryBuilder, $pageable);
        }

        return $queryBuilder->getQuery()->getResult();
    }


    /**
     * Counts invitations with a specific recipient
     *
     * @param User $recipient The recipient
     *
     * @return int The invitations count
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByRecipient(User $recipient) : int
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));
        $this->joinRecipientId($queryBuilder, $recipient->getId());

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    /**
     * Creates a query builder with a filter
     *
     * @param InvitationFilter $filter
     *
     * @return QueryBuilder
     * @throws \Doctrine\ORM\Query\QueryException
     */
    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getRecipientId()))
        {
            $this->joinRecipientId($queryBuilder, $filter->getRecipientId());
        }

        return $queryBuilder;
    }


    private function joinRecipientId(QueryBuilder $queryBuilder, int $recipientId)
    {
        $queryBuilder->join(self::ALIAS . ".recipient", self::RECIPIENT_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::RECIPIENT_ALIAS . ".id", ":recipientId"));
        $queryBuilder->setParameter("recipientId", $recipientId);
    }

}
