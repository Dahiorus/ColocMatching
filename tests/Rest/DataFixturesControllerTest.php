<?php

namespace App\Tests\Rest;

use App\Core\Repository\Filter\Converter\StringConverterInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class DataFixturesControllerTest extends AbstractControllerTest
{
    const FIXTURES_PATH = "tests/Rest/DataFixtures/ORM";

    /** @var StringConverterInterface */
    protected $stringConverter;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $entityManager = static::getService("doctrine.orm.entity_manager");
        static::loadFixtures(static::$kernel, $entityManager);
    }


    public static function tearDownAfterClass()
    {
        $entityManager = self::getService("doctrine.orm.entity_manager");
        static::destroyFixtures($entityManager);

        parent::tearDownAfterClass();
    }


    private static function loadFixtures(KernelInterface $kernel, EntityManagerInterface $entityManager) : void
    {
        $loader = new Loader();
        $path = sprintf("%s/%s", $kernel->getProjectDir(), static::FIXTURES_PATH);
        $loader->loadFromDirectory($path);
        $fixtures = $loader->getFixtures();

        if (empty($fixtures))
        {
            throw new InvalidArgumentException("Could not find any fixtures to load in from [$path]");
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
        $this->stringConverter = self::getService("coloc_matching.core.filter.query_string_converter");
    }


    protected function initTestData() : void
    {
        // empty method
    }


    abstract protected function baseEndpoint() : string;


    abstract protected function searchQueryFilter() : string;


    abstract protected function searchResultAssertCallable() : callable;


    /**
     * @test
     */
    public function getPageShouldReturn200()
    {
        static::$client->request("GET", $this->baseEndpoint(), array ("size" => 10));
        static::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getWithEmptySortsParamShouldReturn200()
    {
        static::$client->request("GET", $this->baseEndpoint(), array ("size" => 5, "sorts" => ""));
        static::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function searchShouldReturn200()
    {
        $filter = $this->searchQueryFilter();

        static::$client->request("GET", $this->baseEndpoint(), array ("q" => $filter, "size" => 5));
        static::assertStatusCode(Response::HTTP_OK);

        $content = $this->getResponseContent();
        static::assertNotNull($content);

        array_walk($content["content"], $this->searchResultAssertCallable());
    }

}
