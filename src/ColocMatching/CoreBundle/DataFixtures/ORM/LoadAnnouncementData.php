<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;

class LoadAnnouncementData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface {

    /** @var ContainerInterface */
    private $container;


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\DependencyInjection\ContainerAwareInterface::setContainer()
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     */
    public function load(ObjectManager $manager) {
        /** @var User */
        $user = $this->getReference("toto");
        /** @var array */
        $data = array ("title" => "Annonce test", "description" => "Annonce creee depuis les DataFixtures",
            "minPrice" => 500, "startDate" => "15/01/2017", "location" => "5 rue des Petits Carreaux, Paris",
            "type" => Announcement::RENT);
        
        try {
            /** @var Announcement */
            $announcement = $this->container->get("coloc_matching.core.announcement_manager")->create($user, $data);
            
            $manager->persist($announcement);
            $manager->persist($user);
        }
        catch (InvalidFormDataException $e) {
            die($e->toJSON());
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

}