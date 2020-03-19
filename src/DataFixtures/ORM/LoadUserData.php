<?php

namespace App\DataFixtures\ORM;

use App\Core\Entity\User\User;
use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserType;
use DateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

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
        /** @var array $jsonUsers */
        $jsonUsers = json_decode(file_get_contents(__DIR__ . "/../Resources/users.json"), true);
        $nbSearches = 0;
        $nbProposals = 0;

        foreach ($jsonUsers as $jsonUser)
        {
            /** @var User $user */
            $user = self::buildUser($jsonUser);

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


    private static function buildUser(array $jsonData) : User
    {
        /** @var User */
        $user = new User($jsonData["email"], "secret1234", $jsonData["firstName"], $jsonData["lastName"]);

        $user->setStatus(UserStatus::ENABLED);
        $user->setPassword(password_hash($user->getPlainPassword(), PASSWORD_BCRYPT, ["cost" => 12]));
        $user->setType($jsonData["type"]);
        $user->setDescription($jsonData["description"]);
        $user->setPhoneNumber($jsonData["phoneNumber"]);

        if (!empty($jsonData["gender"]))
        {
            $user->setGender(strtolower($jsonData["gender"]));
        }

        if (!empty($jsonData["birthDate"]))
        {
            $user->setBirthDate(DateTime::createFromFormat("Y-m-d", $jsonData["birthDate"]));
        }

        return $user;
    }

}
