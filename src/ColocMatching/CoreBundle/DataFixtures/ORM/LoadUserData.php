<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Data fixtures for the Entity User
 *
 * @author Utilisateur
 */
class LoadUserData implements FixtureInterface, ContainerAwareInterface {
    
    /** @var ContainerAwareInterface */
    private $container;
    
    
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }
    
    
    public function load(ObjectManager $manager) {
        try {
        	/* @var \ColocMatching\CoreBundle\Entity\User\User */
        	$user = $this->createUser('test@test.fr', 'password', 'User1', 'Test');
        	
        	$manager->persist($user);
        	$manager->flush();
        } catch (Exception $e) {
        	die ($e->getMessage());
        }
    }
    
    
    private function createUser(string $email, string $password, string $firstname, string $lastname) {
        $user = new User();
        
        $user
            ->setEmail($email)
            ->setPlainPassword($password)
            ->setFirstname($firstname)
            ->setLastname($lastname)
        ;
        $user->setPassword(
            $this->container->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword())
        );
        
       	$errors = $this->container->get('validator')->validate($user);
       	
       	if (count($errors) > 0) {
       		echo $errors;
       		
       		throw new Exception('User data invalid');
       	}
        
        return $user;
    }
    
}
