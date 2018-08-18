<?php

namespace App\Tests\Core;

use Monolog\Logger;
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
        $this->logger = new Logger(get_class($this));
        $this->logger->warning(sprintf("----------------------  Starting test - [%s] -  ----------------------",
            $this->getName()));
    }


    protected function tearDown()
    {
        $this->logger->warning(sprintf("----------------------  Test ended - [%s] -  ----------------------",
            $this->getName()));
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