<?php

namespace App\Tests\Rest;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class DataFixturesControllerTest extends AbstractControllerTest
{
    const FIXTURES_PATH = "/tests/Rest/DataFixtures/ORM";


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
        $path = sprintf("%s/../%s", $kernel->getRootDir(), static::FIXTURES_PATH);
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


    protected function clearData() : void
    {
        // empty method
    }


    protected function initServices() : void
    {
        // empty method
    }


    protected function initTestData() : void
    {
        // empty method
    }


    abstract protected function baseEndpoint() : string;


    abstract protected function searchFilter() : array;


    abstract protected function invalidSearchFilter() : array;


    abstract protected function searchResultAssertCallable() : callable;


    /**
     * @test
     */
    public function getOnePageShouldReturn206()
    {
        static::$client->request("GET", $this->baseEndpoint(), array ("size" => 5));
        self::assertStatusCode(Response::HTTP_PARTIAL_CONTENT);
    }


    /**
     * @test
     */
    public function getAllShouldReturn200()
    {
        static::$client->request("GET", $this->baseEndpoint(), array ("size" => 5000));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function searchShouldReturn200()
    {
        $filter = $this->searchFilter();

        static::$client->request("POST", $this->baseEndpoint() . "/searches", $filter);
        self::assertStatusCode(Response::HTTP_OK);

        $content = $this->getResponseContent();
        self::assertNotNull($content);

        array_walk($content["content"], $this->searchResultAssertCallable());
    }


    /**
     * @test
     */
    public function searchWithInvalidFilterShouldReturn400()
    {
        static::$client->request("POST", $this->baseEndpoint() . "/searches", $this->invalidSearchFilter());
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }

}
