<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAnnouncementData extends AbstractFixture implements OrderedFixtureInterface {

    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     */
    public function load(ObjectManager $manager) {
        $dateFormat = "Y-m-d";
        $types = array (Announcement::TYPE_RENT, Announcement::TYPE_SHARING, Announcement::TYPE_SUBLEASE);

        /** @var array $jsonAnnouncements */
        $jsonAnnouncements = json_decode(file_get_contents(__DIR__ . "/../Resources/announcements.json"), true);
        $nbAnnouncements = 0;

        /** @var array<string> $addresses */
        $addresses = $this->getAddressesFromFile();

        foreach ($jsonAnnouncements as $jsonAnnouncement) {
            /** @var User $creator */
            $creator = $this->getReference("proposal-$nbAnnouncements");
            /** @var Address $location */
            $location = $this->buildAddress($addresses[ random_int(0, count($addresses) - 1) ]);

            /** @var Announcement $announcement */
            $announcement = self::buildAnnouncement($creator, $location, $jsonAnnouncement["title"],
                $jsonAnnouncement["description"], $types[ rand(0, count($types) - 1) ], $jsonAnnouncement["rentPrice"],
                \DateTime::createFromFormat($dateFormat, $jsonAnnouncement["startDate"]),
                (empty($jsonAnnouncement["endDate"]) ? null : \DateTime::createFromFormat($dateFormat,
                    $jsonAnnouncement["endDate"])));

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
        string $type, int $rentPrice, \DateTime $startDate, \DateTime $endDate = null) : Announcement {
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


    private function getAddressesFromFile() : array {
        $addresses = array ();
        $csvFile = fopen(__DIR__ . "/../Resources/addresses.csv", "r");

        while (!feof($csvFile)) {
            $line = fgetcsv($csvFile);

            if (!empty($line)) {
                $addresses[] = sprintf("%s %s", $line[0], "France");
            }
        }

        return $addresses;
    }


    private function buildAddress(string $value) : Address {
        $transformer = new AddressTypeToAddressTransformer("fr", "AIzaSyD2Ie191o1Y3IM5tcVWvpm41EHFTbvuA_8");

        return $transformer->reverseTransform($value);
    }

}