<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Message\GroupConversation;
use ColocMatching\CoreBundle\Repository\Message\GroupConversationRepository;
use Doctrine\ORM\ORMException;

class GroupConversationDao extends EntityDao
{
    /** @var GroupConversationRepository */
    protected $repository;


    /**
     * Finds a group conversation
     *
     * @param Group $group
     *
     * @return GroupConversation|null
     * @throws ORMException
     */
    public function findOneByGroup(Group $group)
    {
        return $this->repository->findOneByGroup($group);
    }


    protected function getDomainClass() : string
    {
        return GroupConversation::class;
    }

}