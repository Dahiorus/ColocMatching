<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;

class LoadAnnouncementData extends AbstractFixture implements OrderedFixtureInterface {
	
	/**
	 * {@inheritDoc}
	 * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
	 */
	public function load(ObjectManager $manager) {
		/** @var User */
		$user = $this->getReference("user-test");
		/** @var Address */
		$location = $this->getReference("5 rue des Petits Carreaux, Paris");
		
		/** @var Announcement */
		$announcement = $this->createAnnouncement($user, "Annonce test", "500", new \DateTime(), $location);
		$user->setAnnouncement($announcement);
		
		$manager->persist($announcement);
		$manager->persist($user);
		$manager->flush();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
	 */
	public function getOrder() {
		return 5;
	}
	
	
	private function createAnnouncement(User $user, string $title, int $minPrice, \DateTime $startDate, Address $location) : Announcement {
		/** @var Announcement */
		$announcement = new Announcement($user);
	
		$announcement
			->setTitle($title)
			->setStartDate($startDate)
			->setMinPrice($minPrice)
			->setLocation($location);
		
		return $announcement;
	}

}