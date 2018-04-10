<?php

namespace ColocMatching\CoreBundle\DAO;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class UserDao extends EntityDao
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }


    protected function getDomainClass() : string
    {
        return User::class;
    }

}