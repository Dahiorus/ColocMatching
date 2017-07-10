<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;
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
                $address = self::buildAddress(sprintf("%s %s", $line[0], "France"));

                $manager->persist($address);
                $this->addReference("address-$nbAddresses", $address);
                $nbAddresses++;

                if ($nbAddresses % 1000 == 0) {
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


    private function buildAddress(string $formattedAddress) : Address {
        $transformer = new AddressTypeToAddressTransformer();

        return $transformer->reverseTransform($formattedAddress);
    }

}