<?php

namespace ColocMatching\CoreBundle\Repository\User;

use ColocMatching\CoreBundle\Entity\User\IdentityProviderAccount;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

class IdentityProviderAccountRepository extends EntityRepository
{
    protected const ALIAS = "pi";


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


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }

}
