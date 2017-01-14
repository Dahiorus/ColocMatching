<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Data fixtures for the Entity User
 *
 * @author Utilisateur
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface {
    
    /** @var ContainerAwareInterface */
    private $container;
    
    
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     */
    public function load(ObjectManager $manager) {
        try {
        	/* @var \ColocMatching\CoreBundle\Entity\User\User */
        	$user = $this->container->get("coloc_matching.core.user_manager")->create(array (
        		"email" => "user3.test@test.fr",
        		"plainPassword" => "password",
        		"firstname" => "User3",
        		"lastname" => "Test"
        	));
        	
        	$manager->persist($user);
        	$manager->flush();
        	
        	$this->addReference("user-test", $user);
        } catch (Exception $e) {
        	die ($e->getMessage());
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     */
    public function getOrder() {
    	return 1;
    }
    }
