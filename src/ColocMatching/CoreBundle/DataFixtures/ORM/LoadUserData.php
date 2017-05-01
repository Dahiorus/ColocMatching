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
        /** @var resource */
        $csvFile = fopen(__DIR__ . "/../Resources/users.csv", "r");
        $nbSearches = 0;
        $nbProposals = 0;

        while (!feof($csvFile)) {
            /** @var array */
            $line = fgetcsv($csvFile);

            if (!empty($line)) {
                /** @var User */
                $user = self::buildUser($line[2], "secret1234", $line[0], $line[1],
                    (($nbSearches + $nbProposals) % 6 == 0) ? UserConstants::TYPE_PROPOSAL : UserConstants::TYPE_SEARCH);

                $manager->persist($user);

                if ($user->getType() == UserConstants::TYPE_PROPOSAL) {
                    $this->addReference("proposal-$nbProposals", $user);
                    $nbProposals++;
                }
                else {
                    $this->addReference("search-$nbSearches", $user);
                    $nbSearches++;
                }

                if (($nbSearches + $nbProposals) % 20 == 0) {
                    $manager->flush();
                }
            }
        }

        $manager->flush();
        fclose($csvFile);
        printf("%d users created.\n", $nbSearches + $nbProposals);
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
