<?php

namespace ColocMatching\CoreBundle\Repository\Invitation;

use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Doctrine\ORM\QueryBuilder;

class InvitationRepository extends EntityRepository
{
    protected const ALIAS = "i";
    private const RECIPIENT_ALIAS = "r";
    private const INVITABLE_ALIAS = "s";


    /**
     * Finds invitations with a specific recipient and paging
     *
     * @param User $recipient The recipient
     * @param PageableFilter $filter
     *
     * @return Invitation[]
     */
    public function findByRecipient(User $recipient, PageableFilter $filter) : array
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->setPaging($queryBuilder, $filter);
        $this->joinRecipientId($queryBuilder, $recipient->getId());

        return $queryBuilder->getQuery()->getResult();
    }


    /**
     * Counts invitations with a specific rescipient
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
     * Finds invitations from the specific announcement or group and with paging
     *
     * @param Invitable $invitable The announcement or group
     * @param PageableFilter $filter Paging information
     *
     * @return Invitation[]
     */
    public function findByInvitable(Invitable $invitable, PageableFilter $filter) : array
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->setPaging($queryBuilder, $filter);
        $this->joinInvitableId($queryBuilder, $invitable->getId());

        return $queryBuilder->getQuery()->getResult();
    }


    /**
     * Counts invitations from the specific announcement or group
     *
     * @param Invitable $invitable The announcement or group
     *
     * @return int The invitations count
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByInvitable(Invitable $invitable) : int
    {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));
        $this->joinInvitableId($queryBuilder, $invitable->getId());

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
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getRecipientId()))
        {
            $this->joinRecipientId($queryBuilder, $filter->getRecipientId());
        }

        if (!empty($filter->getInvitableId()))
        {
            $this->joinInvitableId($queryBuilder, $filter->getInvitableId());
        }

        return $queryBuilder;
    }


    private function joinRecipientId(QueryBuilder $queryBuilder, int $recipientId)
    {
        $queryBuilder->join(self::ALIAS . ".recipient", self::RECIPIENT_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::RECIPIENT_ALIAS . ".id", ":recipientId"));
        $queryBuilder->setParameter("recipientId", $recipientId);
    }


    private function joinInvitableId(QueryBuilder &$queryBuilder, int $invitableId)
    {
        $queryBuilder->join(self::ALIAS . ".invitable", self::INVITABLE_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::INVITABLE_ALIAS . ".id", ":invitableId"));
        $queryBuilder->setParameter("invitableId", $invitableId);
    }
}