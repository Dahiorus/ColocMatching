<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use ColocMatching\CoreBundle\Entity\User\User;

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
        /* @var \ColocMatching\CoreBundle\Entity\User\User */
        $user = LoadUserData::createUser('user1@test.fr', 'user1', 'User1', 'Test');
        
        $manager->persist($user);
        $manager->flush();
    }
    
    
    private static function createUser(string $email, string $password, string $firstname, string $lastname) {
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
    }
    
}
