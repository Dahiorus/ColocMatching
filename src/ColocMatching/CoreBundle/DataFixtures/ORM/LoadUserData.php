<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;

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
    	/** @var array */
    	$datas = array (
    		"h.simpson" => array (
    			"email" => "h.simpson@test.fr",
    			"plainPassword" => "h.simpson",
    			"firstname" => "Homer",
    			"lastname" => "Simpson",
    			"gender" => UserConstants::GENDER_MALE),
    		"m.simpson" => array (
    			"email" => "m.simpson@test.fr",
    			"plainPassword" => "m.simpson",
    			"firstname" => "Marge",
    			"lastname" => "Simpson",
    			"gender" => UserConstants::GENDER_FEMALE),
    		"b.simpson" => array (
    			"email" => "b.simpson@test.fr",
    			"plainPassword" => "b.simpson",
    			"firstname" => "Bart",
   				"lastname" => "Simpson",
    			"gender" => UserConstants::GENDER_MALE),
    		"l.simpson" => array (
    			"email" => "l.simpson@test.fr",
    			"plainPassword" => "l.simpson",
    			"firstname" => "Lisa",
    			"lastname" => "Simpson",
    			"gender" => UserConstants::GENDER_FEMALE),
    		"toto" => array (
    			"email" => "toto@test.fr",
    			"plainPassword" => "password",
    			"firstname" => "Toto",
    			"lastname" => "Test"),
    	);
    	
        try {
        	foreach ($datas as $ref => $data) {
        		/* @var \ColocMatching\CoreBundle\Entity\User\User */
        		$user = $this->container->get("coloc_matching.core.user_manager")->create($data);
        		$this->addReference($ref, $user);
        		
        		$manager->persist($user);
        	}
        	
        	$manager->flush();
        	
        	
        } catch (InvalidFormDataException $e) {
        	die($e->toJSON());
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
