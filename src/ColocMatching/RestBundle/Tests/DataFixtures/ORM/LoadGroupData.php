<?php

namespace ColocMatching\RestBundle\Tests\DataFixtures\ORM;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     */
    public function load(ObjectManager $manager)
    {

        for ($i = 0; $i < 25; $i++)
        {
            /** @var User $creator */
            $creator = $this->getReference("search-$i");

            /** @var Group */
            $group = self::buildGroup($creator, "Group $i", "Description $i", rand(50, 840));

            $manager->persist($group);
            $creator->setGroup($group);
            $manager->persist($creator);
        }

        $manager->flush();
        printf("Groups created.\n");
    }


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     */
    public function getOrder()
    {
        return 20;
    }


    private function buildGroup(User $creator, string $name, ?string $description, int $budget) : Group
    {
        $group = new Group($creator);

        $group->setName($name);
        $group->setDescription($description);
        $group->setBudget($budget);

        return $group;
    }

}