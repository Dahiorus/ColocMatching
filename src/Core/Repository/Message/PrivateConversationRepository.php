<?php

namespace App\Core\Repository\Message;

use App\Core\Entity\Message\PrivateConversation;
use App\Core\Entity\User\User;
use App\Core\Repository\EntityRepository;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\QueryBuilder;

class PrivateConversationRepository extends EntityRepository
{
    protected const ALIAS = "c";

    protected const FIRST_ALIAS = "f";

    protected const SECOND_ALIAS = "s";


    /**
     * Finds private conversations with a specific participant and paging
     *
     * @param User $participant The participant
     * @param Pageable $pageable Paging information
     *
     * @return PrivateConversation[]
     */
    public function findByParticipant(User $participant, Pageable $pageable = null) : array
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->joinParticipant($queryBuilder, $participant);

        if (!empty($pageable))
        {
            $this->setPaging($queryBuilder, $pageable);
        }

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getResult();
    }


    /**
     * Counts private conversations with a specific participant
     *
     * @param User $participant The participant
     *
     * @return int The conversations count
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByParticipant(User $participant) : int
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder->select($queryBuilder->expr()->countDistinct(self::ALIAS));
        $this->joinParticipant($queryBuilder, $participant);

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getSingleScalarResult();
    }


    /**
     * Finds one conversation between 2 participants
     *
     * @param User $first The first participant
     * @param User $second The second participant
     *
     * @return null|PrivateConversation
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByParticipants(User $first, User $second)
    {
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $this->joinParticipants($queryBuilder, $first, $second);

        $query = $queryBuilder->getQuery();
        $query->useQueryCache(true);

        return $query->getOneOrNullResult();
    }


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }


    private function joinParticipant(QueryBuilder $queryBuilder, User $user)
    {
        $queryBuilder->join(self::ALIAS . ".firstParticipant", self::FIRST_ALIAS);
        $queryBuilder->join(self::ALIAS . ".secondParticipant", self::SECOND_ALIAS);

        $queryBuilder->andWhere($queryBuilder->expr()->orX(
            $queryBuilder->expr()->eq(self::FIRST_ALIAS, ":user"),
            $queryBuilder->expr()->eq(self::SECOND_ALIAS, ":user"))
        );
        $queryBuilder->setParameter("user", $user);
    }


    private function joinParticipants(QueryBuilder $queryBuilder, User $first, User $second)
    {
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
