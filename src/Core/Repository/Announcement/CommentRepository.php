<?php

namespace App\Core\Repository\Announcement;

use App\Core\Entity\Announcement\Comment;
use App\Core\Entity\User\User;
use App\Core\Repository\EntityRepository;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\QueryBuilder;

class CommentRepository extends EntityRepository
{
    protected const ALIAS = "c";
    private const AUTHOR_ALIAS = "ca";


    /**
     * Finds comments with the specified author with paging
     *
     * @param User $author The comments author
     * @param Pageable $pageable [optional] Paging information
     *
     * @return Comment[]
     */
    public function findByAuthor(User $author, Pageable $pageable = null) : array
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        if (!empty($pageable))
        {
            $this->setPaging($qb, $pageable);
        }

        $this->joinAuthor($qb, $author);

        return $qb->getQuery()->getResult();
    }


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }


    private function joinAuthor(QueryBuilder $qb, User $user)
    {
        $qb->join(self::ALIAS . ".author", self::AUTHOR_ALIAS);
        $qb->andWhere($qb->expr()->eq(self::AUTHOR_ALIAS, ":user"));
        $qb->setParameter("user", $user);
    }

}