<?php

namespace ColocMatching\CoreBundle\Repository\Message;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Doctrine\ORM\QueryBuilder;

class PrivateConversationRepository extends EntityRepository {

    protected const ALIAS = "c";

    protected const FIRST_ALIAS = "f";

    protected const SECOND_ALIAS = "s";


    public function findByParticipant(PageableFilter $filter, User $participant) : array {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->setPagination($queryBuilder, $filter, self::ALIAS);
        $this->joinParticipant($queryBuilder, $participant);

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByParticipant(User $participant) : int {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));
        $this->joinParticipant($queryBuilder, $participant);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    public function findOneByParticipants(User $first, User $second) {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->joinParticipants($queryBuilder, $first, $second);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }


    private function joinParticipant(QueryBuilder &$queryBuilder, User $user) {
        $queryBuilder->join(self::ALIAS . ".firstParticipant", self::FIRST_ALIAS);
        $queryBuilder->join(self::ALIAS . ".secondParticipant", self::SECOND_ALIAS);

        $queryBuilder->andWhere($queryBuilder->expr()->orX(
            $queryBuilder->expr()->eq(self::FIRST_ALIAS, ":user"),
            $queryBuilder->expr()->eq(self::SECOND_ALIAS, ":user"))
        );
        $queryBuilder->setParameter("user", $user);
    }


    private function joinParticipants(QueryBuilder &$queryBuilder, User $first, User $second) {
        $queryBuilder->join(self::ALIAS . ".firstParticipant", self::FIRST_ALIAS)
            ->join(self::ALIAS . ".secondParticipant", self::SECOND_ALIAS);

        $queryBuilder->orWhere($queryBuilder->expr()->andX(
            $queryBuilder->expr()->eq(self::FIRST_ALIAS, ":first"),
            $queryBuilder->expr()->eq(self::SECOND_ALIAS, ":second"))
        );
        $queryBuilder->orWhere($queryBuilder->expr()->andX(
            $queryBuilder->expr()->eq(self::FIRST_ALIAS, ":second"),
            $queryBuilder->expr()->eq(self::SECOND_ALIAS, ":first"))
        );
        $queryBuilder->setParameters(array ("first" => $first, "second" => $second));
    }
}