<?php

namespace App\Core\Repository\Message;

use App\Core\Entity\Message\GroupMessage;
use App\Core\Entity\User\User;
use App\Core\Repository\EntityRepository;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\QueryBuilder;

class GroupMessageRepository extends EntityRepository
{
    protected const ALIAS = "m";
    private const AUTHOR_ALIAS = "a";


    /**
     * Finds group message with the user as the author with paging
     *
     * @param User $author The message author
     * @param Pageable|null $pageable Paging information
     *
     * @return GroupMessage[]
     */
    public function findByAuthor(User $author, Pageable $pageable = null) : array
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        $this->joinAuthor($qb, $author);

        if (!empty($pageable))
        {
            $this->setPaging($qb, $pageable);
        }

        return $qb->getQuery()->getResult();
    }


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }


    private function joinAuthor(QueryBuilder $queryBuilder, User $author)
    {
        $queryBuilder->join(self::ALIAS . ".author", self::AUTHOR_ALIAS);
        $queryBuilder->where($queryBuilder->expr()->eq(self::AUTHOR_ALIAS, ":author"));
        $queryBuilder->setParameter("author", $author);
    }

}