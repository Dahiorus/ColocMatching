<?php

namespace App\Core\Repository\User;

use App\Core\Repository\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class UserTokenRepository extends EntityRepository
{
    protected const ALIAS = "t";


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(static::ALIAS);
    }

}
