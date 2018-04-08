<?php

namespace ColocMatching\RestBundle\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class DataFixturesControllerTest extends AbstractControllerTest
{
    const FIXTURES_PATH = "/Tests/DataFixtures/ORM";


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $entityManager = self::getService("doctrine.orm.entity_manager");
        self::loadFixtures(static::$kernel, $entityManager);
    }


    public static function tearDownAfterClass()
    {
        $entityManager = self::getService("doctrine.orm.entity_manager");
        self::destroyFixtures($entityManager);

        parent::tearDownAfterClass();
    }


    private static function loadFixtures(KernelInterface $kernel, EntityManagerInterface $entityManager) : void
    {
        $loader = new Loader();
        $bundle = $kernel->getBundle("RestBundle");
        $path = $bundle->getPath() . static::FIXTURES_PATH;

        $loader->loadFromDirectory($path);

        $fixtures = $loader->getFixtures();

        if (empty($fixtures))
        {
            throw new \InvalidArgumentException("Could not find any fixtures to load in");
        }

        $executor = new ORMExecutor($entityManager, new ORMPurger($entityManager));
        $executor->execute($fixtures);
    }


    private static function destroyFixtures(EntityManagerInterface $entityManager)
    {
        $purger = new ORMPurger($entityManager);
        $purger->purge();
    }
}