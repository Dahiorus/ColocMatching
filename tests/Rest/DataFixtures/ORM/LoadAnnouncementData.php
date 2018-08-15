<?php

namespace App\Tests\Rest\DataFixtures\ORM;

use App\Core\Entity\Announcement\Address;
use App\Core\Entity\Announcement\Announcement;
use App\Core\Entity\User\User;
use App\Core\Form\DataTransformer\AddressTypeToAddressTransformer;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAnnouncementData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $types = array (Announcement::TYPE_RENT, Announcement::TYPE_SHARING, Announcement::TYPE_SUBLEASE);

        $num = rand(1, 20);
        $address = "Paris 750" . ($num < 10 ? "0" : "") . $num;

        for ($i = 0; $i < 25; $i++)
        {
            /** @var User $creator */
            $creator = $this->getReference("proposal-$i");
            /** @var Address $location */
            $location = $this->buildAddress($address);

            /** @var Announcement $announcement */
            $announcement = self::buildAnnouncement($creator, $location, "Announcement $i",
                "Description $i", $types[ rand(0, count($types) - 1) ], rand(350, 1500),
                new \DateTime(), null);

            $manager->persist($announcement);
            $creator->setAnnouncement($announcement);
            $manager->merge($creator);
        }

        $manager->flush();
        printf("Announcements created.\n");
    }


    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10;
    }


    private function buildAnnouncement(User $creator, Address $location, string $title, ?string $description,
        string $type, int $rentPrice, \DateTime $startDate, \DateTime $endDate = null) : Announcement
    {
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


    private function buildAddress(string $value) : Address
    {
        $transformer = new AddressTypeToAddressTransformer("fr", "AIzaSyD2Ie191o1Y3IM5tcVWvpm41EHFTbvuA_8");

        return $transformer->reverseTransform($value);
    }

}