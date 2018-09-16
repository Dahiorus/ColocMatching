<?php

namespace App\DataFixtures\ORM;

use App\Core\Entity\User\User;
use App\Core\Entity\User\UserConstants;
use App\Core\Entity\User\UserType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Data fixtures for the Entity User
 *
 * @author Dahiorus
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     */
    public function load(ObjectManager $manager)
    {
        /** @var array */
        $jsonUsers = json_decode(file_get_contents(__DIR__ . "/../Resources/users.json"), true);
        $nbSearches = 0;
        $nbProposals = 0;

        foreach ($jsonUsers as $jsonUser)
        {
            /** @var User */
            $user = self::buildUser($jsonUser["email"], "secret1234", $jsonUser["firstname"], $jsonUser["lastname"],
                (($nbSearches + $nbProposals) % 2 == 0) ? UserType::PROPOSAL : UserType::SEARCH);

            $manager->persist($user);

            if ($user->getType() == UserType::PROPOSAL)
            {
                $this->addReference("proposal-$nbProposals", $user);
                $nbProposals++;
            }
            else
            {
                $this->addReference("search-$nbSearches", $user);
                $nbSearches++;
            }

            if (($nbSearches + $nbProposals) % 1000 == 0)
            {
                $manager->flush();
            }
        }

        $manager->flush();
        printf("%d users created.\n", $nbSearches + $nbProposals);
    }


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     */
    public function getOrder()
    {
        return 1;
    }


    private static function buildUser(string $email, string $plainPassword, string $firstname, string $lastname,
        string $type) : User
    {
        /** @var User */
        $user = new User($email, $plainPassword, $firstname, $lastname);

        $user->setPassword(password_hash($plainPassword, PASSWORD_BCRYPT, ["cost" => 12]));
        $user->setType($type);
        $user->setStatus(UserConstants::STATUS_ENABLED);

        return $user;
    }

}
