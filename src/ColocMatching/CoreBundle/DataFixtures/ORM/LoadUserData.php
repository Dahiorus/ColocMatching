<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ColocMatching\CoreBundle\Entity\User\UserConstants;

/**
 * Data fixtures for the Entity User
 *
 * @author Utilisateur
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface {


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
                "enabled" => true,
                "type" => UserConstants::TYPE_PROPOSAL),
            "m.simpson" => array (
                "email" => "m.simpson@test.fr",
                "plainPassword" => "m.simpson",
                "firstname" => "Marge",
                "lastname" => "Simpson",
                "type" => UserConstants::TYPE_PROPOSAL),
            "b.simpson" => array (
                "email" => "b.simpson@test.fr",
                "plainPassword" => "b.simpson",
                "firstname" => "Bart",
                "lastname" => "Simpson",
                "type" => UserConstants::TYPE_SEARCH),
            "l.simpson" => array (
                "email" => "l.simpson@test.fr",
                "plainPassword" => "l.simpson",
                "firstname" => "Lisa",
                "lastname" => "Simpson",
                "type" => UserConstants::TYPE_SEARCH),
            "toto" => array (
                "email" => "toto@test.fr",
                "plainPassword" => "password",
                "firstname" => "Toto",
                "lastname" => "Test",
                "enabled" => true,
                "type" => UserConstants::TYPE_PROPOSAL));

        foreach ($datas as $ref => $data) {
            $user = self::buildUser($data["email"], $data["plainPassword"], $data["firstname"], $data["lastname"],
                $data["type"]);

            if (!empty($data["enabled"])) {
                $user->setEnabled($data["enabled"]);
            }

            $manager->persist($user);
            $this->addReference($ref, $user);
        }

        $manager->flush();
    }


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     */
    public function getOrder() {
        return 1;
    }


    private static function buildUser(string $email, string $plainPassword, string $firstname, string $lastname,
        string $type): User {
        /** @var User */
        $user = new User();

        $user->setEmail($email);
        $user->setPlainPassword($plainPassword);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setPassword(password_hash($plainPassword, PASSWORD_BCRYPT, [ "cost" => 12]));
        $user->setType($type);

        return $user;
    }

}
