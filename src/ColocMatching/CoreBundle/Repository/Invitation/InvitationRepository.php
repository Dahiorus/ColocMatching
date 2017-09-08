<?php

namespace ColocMatching\CoreBundle\Repository\Invitation;

use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Doctrine\ORM\QueryBuilder;

class InvitationRepository extends EntityRepository {

    protected const ALIAS = "i";
    private const RECIPIENT_ALIAS = "r";
    private const INVITABLE_ALIAS = "s";


    public function findByFilter(InvitationFilter $filter, array $fields = null) : array {
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $this->setPagination($queryBuilder, $filter, self::ALIAS);

        if (!empty($fields)) {
            $queryBuilder->select($this->getReturnedFields(self::ALIAS, $fields));
        }

        return $queryBuilder->getQuery()->getResult();
    }


    public function findAllBy(InvitationFilter $filter) : array {
        $queryBuilder = $this->createFilterQueryBuilder($filter);

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByFilter(InvitationFilter $filter) : int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createFilterQueryBuilder($filter);
        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    public function findByRecipient(User $recipient, PageableFilter $filter) : array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->setPagination($queryBuilder, $filter, self::ALIAS);
        $this->joinRecipientId($queryBuilder, $recipient->getId());

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByRecipient(User $recipient) : int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));
        $this->joinRecipientId($queryBuilder, $recipient->getId());

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    public function findByInvitable(Invitable $invitable, PageableFilter $filter) : array {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->setPagination($queryBuilder, $filter, self::ALIAS);
        $this->joinInvitableId($queryBuilder, $invitable->getId());

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByInvitable(Invitable $invitable) : int {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));
        $this->joinInvitableId($queryBuilder, $invitable->getId());

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    private function createFilterQueryBuilder(InvitationFilter $filter) : QueryBuilder {
        /** @var QueryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);
        $queryBuilder->addCriteria($filter->buildCriteria());

        if (!empty($filter->getRecipientId())) {
            $this->joinRecipientId($queryBuilder, $filter->getRecipientId());
        }

        if (!empty($filter->getInvitableId())) {
            $this->joinInvitableId($queryBuilder, $filter->getInvitableId());
        }

        return $queryBuilder;
    }


    private function joinRecipientId(QueryBuilder &$queryBuilder, int $recipientId) {
        $queryBuilder->join(self::ALIAS . ".recipient", self::RECIPIENT_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::RECIPIENT_ALIAS . ".id", ":recipientId"));
        $queryBuilder->setParameter("recipientId", $recipientId);
    }


    private function joinInvitableId(QueryBuilder &$queryBuilder, int $invitableId) {
        $queryBuilder->join(self::ALIAS . ".invitable", self::INVITABLE_ALIAS);
        $queryBuilder->andWhere($queryBuilder->expr()->eq(self::INVITABLE_ALIAS . ".id", ":invitableId"));
        $queryBuilder->setParameter("invitableId", $invitableId);
    }
}