<?php

namespace ColocMatching\CoreBundle\Repository\Message;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Doctrine\ORM\QueryBuilder;

class PrivateConversationRepository extends EntityRepository {

    protected const ALIAS = "pm";

    private const FIRST_ALIAS = "f";

    private const SECOND_ALIAS = "s";


    public function findByParticipant(PageableFilter $filter, User $participant) : array {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->setPagination($queryBuilder, $filter, self::ALIAS);
        $this->joinParticipant($queryBuilder, $participant);

        return $queryBuilder->getQuery()->getResult();
    }


    public function countByParticipant(User $participant) : int {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->joinParticipant($queryBuilder, $participant);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    public function findOneByParticipants(User $first, User $second) {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->joinParticipant($queryBuilder, $first);
        $this->joinParticipant($queryBuilder, $second);

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
}