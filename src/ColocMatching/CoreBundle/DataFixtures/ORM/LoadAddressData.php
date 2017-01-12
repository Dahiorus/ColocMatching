<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;
use ColocMatching\CoreBundle\Entity\Announcement\Address;

class LoadAddressData extends AbstractFixture implements OrderedFixtureInterface {

	/**
	 * {@inheritDoc}
	 * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
	 */
	public function load(ObjectManager $manager) {
		$strAddresses = array(
			"5 rue des Petits Carreaux, Paris",
		);
		
		$addressTranformer = new AddressTypeToAddressTransformer();
		
		foreach ($strAddresses as $strAddress) {
			/** @var Address */
			$address = $addressTranformer->reverseTransform($strAddress);
			
			$manager->persist($address);
			$this->addReference($strAddress, $address);
		}
		
		$manager->flush();
	}


	/**
	 * {@inheritDoc}
	 * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
	 */
	public function getOrder() {
		return 4;
	}

}