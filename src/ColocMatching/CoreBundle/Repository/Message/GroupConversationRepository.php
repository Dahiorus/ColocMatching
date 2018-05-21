<?php

namespace ColocMatching\CoreBundle\Repository\Message;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Message\GroupConversation;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

class GroupConversationRepository extends EntityRepository
{
    protected const ALIAS = "c";

    private const GROUP_ALIAS = "g";


    /**
     * Finds one group conversation
     *
     * @param Group $group The group
     *
     * @return GroupConversation|null
     * @throws NonUniqueResultException
     */
    public function findOneByGroup(Group $group)
    {
        $qb = $this->createQueryBuilder(self::ALIAS);
        $this->joinGroup($qb, $group);

        $query = $qb->getQuery();
        $this->configureCache($query);

        return $query->getOneOrNullResult();
    }


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }


    private function joinGroup(QueryBuilder $qb, Group $group)
    {
        $qb->join(self::ALIAS . ".group", self::GROUP_ALIAS);

        $qb->andWhere($qb->expr()->eq(self::GROUP_ALIAS, ":group"));
        $qb->setParameter("group", $group);
    }

}
