<?php

namespace App\Tests\Rest\DataFixtures\ORM;

use App\Core\Entity\User\User;
use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Data fixtures for the Entity User
 *
 * @author Utilisateur
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var array */
        $nbSearches = 0;
        $nbProposals = 0;

        for ($i = 1; $i <= 30; $i++)
        {
            /** @var User */
            $user = self::buildUser("user-$i@test.fr", "secret1234", "User-$i", "Test-$i",
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
        }

        $manager->flush();
    }


    /**
     * {@inheritDoc}
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

        $user->setType($type);

        $index = rand(0, 3);
        $status = array (UserStatus::BANNED, UserStatus::ENABLED, UserStatus::PENDING, UserStatus::VACATION)[ $index ];
        $user->setStatus($status);

        return $user;
    }

}
