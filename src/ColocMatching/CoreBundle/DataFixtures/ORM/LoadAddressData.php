<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAddressData extends AbstractFixture implements OrderedFixtureInterface {


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     */
    public function load(ObjectManager $manager) {
        /** @var resource */
        $csvFile = fopen(__DIR__ . "/../Resources/addresses.csv", "r");
        $nbAddresses = 0;

        while (!feof($csvFile)) {
            $line = fgetcsv($csvFile);

            if (!empty($line)) {
                /** @var Address */
                $address = self::buildAddress(floatval($line[0]), floatval($line[1]));

                $manager->persist($address);
                $this->addReference("address-$nbAddresses", $address);
                $nbAddresses++;

                if ($nbAddresses % 20 == 0) {
                    $manager->flush();
                }
            }
        }

        $manager->flush();
        fclose($csvFile);
        printf("%d addresses created.\n", $nbAddresses);
    }


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     */
    public function getOrder() {
        return 5;
    }


    private function buildAddress(float $lat, float $lng): Address {
        $address = new Address();

        $address->setLocality("Paris");
        $address->setLat($lat);
        $address->setLng($lng);

        return $address;
    }

}