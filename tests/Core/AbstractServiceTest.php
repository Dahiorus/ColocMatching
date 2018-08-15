<?php

namespace App\Tests\Core;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

abstract class AbstractServiceTest extends KernelTestCase
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var array */
    private static $services = array ();


    public static function setUpBeforeClass()
    {
        self::bootKernel();
    }


    public static function tearDownAfterClass()
    {
        self::ensureKernelShutdown();
    }


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        $this->logger = $this->getService("logger");
        $this->logger->info("----------------------  Starting test  ----------------------",
            array ("test" => $this->getName()));
    }


    protected function tearDown()
    {
        $this->logger->info("----------------------  End test  ----------------------",
            array ("test" => $this->getName()));
    }


    /**
     * Gets a service component corresponding to the identifier
     *
     * @param string $serviceId The service unique identifier
     *
     * @return mixed The service
     * @throws ServiceNotFoundException
     */
    protected function getService(string $serviceId)
    {
        if (empty(self::$services[ $serviceId ]))
        {
            self::$services[ $serviceId ] = static::$kernel->getContainer()->get($serviceId);
        }

        return self::$services[ $serviceId ];
    }

}