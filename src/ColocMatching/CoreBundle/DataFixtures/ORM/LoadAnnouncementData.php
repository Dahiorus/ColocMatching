<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
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
        $dateFormat = "d/m/Y";

        $userRefs = array ("toto", "m.simpson", "h.simpson");
        $addressRefs = array ("198 avenue d'Italie, Paris", "78 rue de Rivoli, Paris", "Paris");
        $minPrices = array (530, 380, 950);
        $startDates = array ("03/03/2017", "05/06/2017", "09/07/2017");
        $types = array (Announcement::TYPE_RENT, Announcement::TYPE_RENT, Announcement::TYPE_SHARING);

        for ($i = 0; $i < 3; $i++) {
            $creator = $this->getReference($userRefs[$i]);
            $location = $this->getReference($addressRefs[$i]);

            $announcement = $this->buildAnnouncement($creator, $location, "Annonce $i", $types[$i], $minPrices[$i],
                \DateTime::createFromFormat($dateFormat, $startDates[$i]));
            $announcement->setDescription("Annonce créée depuis les DataFixtures");
            $creator->setAnnouncement($announcement);

            $manager->persist($announcement);
            $manager->merge($creator);
            $this->addReference("announcement-$i", $announcement);
        }

        $manager->flush();
    }


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     */
    public function getOrder() {
        return 10;
    }


    private function buildAnnouncement(User $creator, Address $location, string $title, string $type, int $minPrice,
        \DateTime $startDate): Announcement {
        $announcement = new Announcement($creator);

        $announcement->setLocation($location);
        $announcement->setTitle($title);
        $announcement->setType($type);
        $announcement->setMinPrice($minPrice);
        $announcement->setStartDate($startDate);

        return $announcement;
    }

}