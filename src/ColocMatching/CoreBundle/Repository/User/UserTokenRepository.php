<?php

namespace ColocMatching\CoreBundle\Repository\User;

use ColocMatching\CoreBundle\Repository\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class UserTokenRepository extends EntityRepository
{
    protected const ALIAS = "t";


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(static::ALIAS);
    }

}
