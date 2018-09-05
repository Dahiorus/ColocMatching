<?php

namespace App\Tests;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

abstract class AbstractServiceTest extends KernelTestCase
{
    /** @var LoggerInterface */
    protected $logger;


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
        $this->logger->warning(sprintf("----------------------  Starting test - [ %s :: %s ] -  ----------------------",
            get_class($this), $this->getName()));
    }


    protected function tearDown()
    {
        $this->logger->warning(sprintf("----------------------  Test ended - [ %s :: %s ] -  ----------------------",
            get_class($this), $this->getName()));
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
        return static::$container->get($serviceId);
    }

}