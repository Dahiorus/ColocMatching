<?php

namespace ColocMatching\CoreBundle\DataFixtures\ORM;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface {


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     */
    public function load(ObjectManager $manager) {
        /** @var array */
        $jsonGroups = json_decode(file_get_contents(__DIR__ . "/../Resources/groups.json"), true);
        $nbGroups = 0;

        foreach ($jsonGroups as $jsonGroup) {
            /** @var User */
            $creator = $this->getReference("search-$nbGroups");

            /** @var Group */
            $group = self::buildGroup($creator, $jsonGroup["name"], $jsonGroup["description"]);

            $manager->persist($group);
            $creator->setGroup($group);
            $manager->persist($creator);

            $nbGroups++;

            if ($nbGroups % 1000 == 0) {
                $manager->flush();
            }
        }

        $manager->flush();
        printf("%d groups created.\n", $nbGroups);
    }


    /**
     * {@inheritDoc}
     * @see \Doctrine\Common\DataFixtures\OrderedFixtureInterface::getOrder()
     */
    public function getOrder() {
        return 20;
    }


    private function buildGroup(User $creator, string $name, ?string $description): Group {
        $group = new Group($creator);

        $group->setName($name);
        $group->setDescription($description);

        return $group;
    }

}