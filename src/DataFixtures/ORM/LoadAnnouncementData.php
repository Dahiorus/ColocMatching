<?php

namespace App\DataFixtures\ORM;

use App\Core\Entity\Announcement\Address;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\User\User;
use App\Core\Form\DataTransformer\StringToAddressTransformer;
use DateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAnnouncementData extends AbstractFixture implements OrderedFixtureInterface
{
    private const DATE_FORMAT = "Y-m-d";


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     */
    public function load(ObjectManager $manager)
    {
        /** @var array $jsonAnnouncements */
        $jsonAnnouncements = json_decode(file_get_contents(__DIR__ . "/../Resources/announcements.json"), true);
        $nbAnnouncements = 0;

        /** @var string[] $addresses */
        $addresses = $this->getAddressesFromFile();

        foreach ($jsonAnnouncements as $jsonAnnouncement)
        {
            /** @var User $creator */
            $creator = $this->getReference("proposal-$nbAnnouncements");
            /** @var Address $location */
            $location = $this->buildAddress($addresses[ random_int(0, count($addresses) - 1) ]);

            /** @var Announcement $announcement */
            $announcement = self::buildAnnouncement($creator, $location, $jsonAnnouncement);

            $manager->persist($announcement);
            $creator->addAnnouncement($announcement);
            $manager->merge($creator);

            $nbAnnouncements++;

            if ($nbAnnouncements % 1000 == 0)
            {
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
    public function getOrder()
    {
        return 10;
    }


    private function buildAnnouncement(User $creator, Address $location, array $json) : Announcement
    {
        $announcement = new Announcement($creator);

        $announcement->setLocation($location);
        $announcement->setTitle($json["title"]);
        $announcement->setDescription($json["description"]);
        $announcement->setType($json["type"]);
        $announcement->setRentPrice($json["rentPrice"]);
        $announcement->setStartDate(DateTime::createFromFormat(self::DATE_FORMAT, $json["startDate"]));
        $announcement->setEndDate(
            empty($json["endDate"]) ? null : DateTime::createFromFormat(self::DATE_FORMAT, $json["endDate"]));
        $announcement->setStatus($json["status"]);
        $announcement->setHousingType($json["housingType"]);
        $announcement->setRoomCount($json["roomCount"]);
        $announcement->setBedroomCount($json["bedroomCount"]);
        $announcement->setBathroomCount($json["bathroomCount"]);
        $announcement->setSurfaceArea($json["surfaceArea"]);
        $announcement->setRoomMateCount($json["roomMateCount"]);

        return $announcement;
    }


    private function getAddressesFromFile() : array
    {
        $addresses = array ();
        $csvFile = fopen(__DIR__ . "/../Resources/addresses.csv", "r");

        while (!feof($csvFile))
        {
            $line = fgetcsv($csvFile);

            if (!empty($line))
            {
                $addresses[] = sprintf("%s %s", $line[0], "France");
            }
        }

        return $addresses;
    }


    private function buildAddress(string $value) : Address
    {
        $transformer = new StringToAddressTransformer("fr", "AIzaSyD2Ie191o1Y3IM5tcVWvpm41EHFTbvuA_8");

        return $transformer->reverseTransform($value);
    }

}
