<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Appartoo\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Appartoo\CoreBundle\Entity\User\User;

/**
 * Data fixtures for the Entity User
 *
 * @author Utilisateur
 */
class LoadUserData implements FixtureInterface {
    
    public function load(ObjectManager $manager) {
        /* @var \Appartoo\CoreBundle\Entity\User\User */
        $user = new User('user.test@test.fr', 'secret', 'Test', 'User');
        
        $manager->persist($user);
        $manager->flush();
    }
    
}
