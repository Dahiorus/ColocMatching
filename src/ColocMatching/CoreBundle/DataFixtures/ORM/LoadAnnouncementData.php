<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAnnouncementData extends AbstractFixture implements OrderedFixtureInterface {


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     */
    public function load(ObjectManager $manager) {
        $dateFormat = "d/m/Y";
        $types = array (Announcement::TYPE_RENT, Announcement::TYPE_SHARING, Announcement::TYPE_SUBLEASE);

        /** @var array */
        $jsonAnnouncements = json_decode(file_get_contents(__DIR__ . "/../Resources/announcements.json"), true);
        $nbAnnouncements = 0;

        foreach ($jsonAnnouncements as $jsonAnnouncement) {
            /** @var User */
            $creator = $this->getReference("proposal-$nbAnnouncements");
            /** @var Address */
            $location = $this->getReference("address-$nbAnnouncements");

            /** @var Announcement */
            $announcement = self::buildAnnouncement($creator, $location, $jsonAnnouncement["title"],
                $jsonAnnouncement["description"], $types[rand(0, count($types) - 1)], $jsonAnnouncement["rentPrice"],
                \DateTime::createFromFormat($dateFormat, $jsonAnnouncement["startDate"]),
                empty($jsonAnnouncement["endDate"]) ? null : \DateTime::createFromFormat($dateFormat,
                    $jsonAnnouncement["endDate"]));

            $manager->persist($announcement);
            $creator->setAnnouncement($announcement);
            $manager->merge($creator);

            $nbAnnouncements++;

            if ($nbAnnouncements % 1000 == 0) {
                $manager->flush();
            }
        }

        $manager->flush();
        printf("%d announcements created.\n", $nbAnnouncements);
    }


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     */
    public function getOrder() {
        return 10;
    }


    private function buildAnnouncement(User $creator, Address $location, string $title, ?string $description,
        string $type, int $rentPrice, \DateTime $startDate, \DateTime $endDate = null): Announcement {
        $announcement = new Announcement($creator);

        $announcement->setLocation($location);
        $announcement->setTitle($title);
        $announcement->setDescription($description);
        $announcement->setType($type);
        $announcement->setRentPrice($rentPrice);
        $announcement->setStartDate($startDate);
        $announcement->setEndDate($endDate);

        return $announcement;
    }

}