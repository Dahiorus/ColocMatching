<?php

namespace App\Core\Repository\User;

use App\Core\Entity\User\IdentityProviderAccount;
use App\Core\Entity\User\User;
use App\Core\Repository\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

class IdentityProviderAccountRepository extends EntityRepository
{
    protected const ALIAS = "idp";
    private const USER_ALIAS = "u";


    /**
     * Finds one external identity matching the provider name and external ID. Can return null.
     *
     * @param string $providerName The external provider name
     * @param string $externalId The external identifier
     *
     * @return IdentityProviderAccount|null
     * @throws NonUniqueResultException
     */
    public function findOneByProvider(string $providerName, string $externalId)
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq(self::ALIAS . ".providerName", ":providerName"),
                $qb->expr()->eq(self::ALIAS . ".externalId", ":externalId")
            ));
        $qb->setParameter("providerName", $providerName);
        $qb->setParameter("externalId", $externalId);

        $query = $qb->getQuery();
        $query->useQueryCache(true);

        return $query->getOneOrNullResult();
    }


    /**
     * Finds all user IdP accounts
     *
     * @param User $user The user
     * @return IdentityProviderAccount[]
     */
    public function findByUser(User $user) : array
    {
        $qb = $this->createQueryBuilder(self::ALIAS);
        $qb->join(self::ALIAS . ".user", self::USER_ALIAS);
        $qb->where($qb->expr()->eq(self::USER_ALIAS, ":user"));
        $qb->setParameter("user", $user);

        return $qb->getQuery()->getResult();
    }


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }

}
