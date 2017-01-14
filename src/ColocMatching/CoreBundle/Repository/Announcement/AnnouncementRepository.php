<?php

namespace ColocMatching\CoreBundle\Repository\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Repository\EntityRepository;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use Doctrine\ORM\QueryBuilder;

/**
 * AnnouncementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnnouncementRepository extends EntityRepository
{

	public function findByAddress(Address $address, AbstractFilter $filter) : array {
		/** @var QueryBuilder */
		$queryBuilder = $this->createQueryBuilder("a");
		
		$this->setPagination($queryBuilder, $filter);
		$this->setOrderBy($queryBuilder, $filter, "a");
		$this->joinAddress($queryBuilder, $address);
		
		return $queryBuilder->getQuery()->getResult();
	}
	

	public function selectFieldsByAddress(Address $address, array $fields, AbstractFilter $filter) : array {
		/** @var QueryBuilder */
		$queryBuilder = $this->createQueryBuilder("a");
		
		$queryBuilder->select($this->getReturnedFields("a", $fields));
		$this->setPagination($queryBuilder, $filter);
		$this->setOrderBy($queryBuilder, $filter, "a");
		$this->joinAddress($queryBuilder, $address);
		
		return $queryBuilder->getQuery()->getResult();
	}

	
	private function joinAddress(QueryBuilder &$queryBuilder, Address $address) {
		$queryBuilder->join("a.location", "l");
		
		if (!empty($address->getStreetNumber())) {
			$queryBuilder->andWhere($queryBuilder->expr()->eq("l.streetNumber", $address->getStreetNumber()));
		}
		
		if (!empty($address->getRoute())) {
			$queryBuilder->andWhere($queryBuilder->expr()->eq("l.route", $address->getRoute()));
		}
		
		if (!empty($address->getLocality())) {
			$queryBuilder->andWhere($queryBuilder->expr()->eq("l.locality", $address->getLocality()));
		}
		
		if (!empty($address->getCountry())) {
			$queryBuilder->andWhere($queryBuilder->expr()->eq("l.country", $address->getCountry()));
		}
		
		if (!empty($address->getZipCode())) {
			$queryBuilder->andWhere($queryBuilder->expr()->eq("l.zipCode", $address->getZipCode()));
		}
	}
}
