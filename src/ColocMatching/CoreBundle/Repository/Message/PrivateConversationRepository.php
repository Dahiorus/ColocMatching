<?php

namespace ColocMatching\CoreBundle\Repository\Message;

use ColocMatching\CoreBundle\Entity\User\PrivateConversation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\Pageable;
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

        return $queryBuilder->getQuery()->getResult();
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

        return $queryBuilder->getQuery()->getSingleScalarResult();
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

        return $queryBuilder->getQuery()->getOneOrNullResult();
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