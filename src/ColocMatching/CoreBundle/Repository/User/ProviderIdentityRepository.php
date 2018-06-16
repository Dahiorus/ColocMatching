<?php

namespace ColocMatching\CoreBundle\Repository\User;

use ColocMatching\CoreBundle\Entity\User\ProviderIdentity;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

class ProviderIdentityRepository extends EntityRepository
{
    protected const ALIAS = "ei";


    /**
     * Finds one external identity matching the provider name and external ID. Can return null.
     *
     * @param string $providerName The external provider name
     * @param string $externalId The external identifier
     *
     * @return ProviderIdentity|null
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

        $this->configureCache($query);

        return $query->getOneOrNullResult();
    }


    protected function createFilterQueryBuilder($filter) : QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }

}
