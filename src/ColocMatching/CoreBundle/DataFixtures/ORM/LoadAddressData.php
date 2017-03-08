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
        $formattedAddresses = array (
            "198 avenue d'Italie, Paris",
            "100 avenue d'Ivry, Paris",
            "5 rue des Petits Carreaux, Paris",
            "78 rue de Rivoli, Paris",
            "Paris");

        foreach ($formattedAddresses as $formattedAddress) {
            $address = $this->buildAddress($formattedAddress);

            $manager->persist($address);
            $this->setReference($formattedAddress, $address);
        }

        $manager->flush();
    }


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     */
    public function getOrder() {
        return 5;
    }


    private function buildAddress(string $formattedAddress): Address {
        $transformer = new AddressTypeToAddressTransformer();

        return $transformer->reverseTransform($formattedAddress);
    }

}