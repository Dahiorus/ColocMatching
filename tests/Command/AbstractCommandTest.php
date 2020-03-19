<?php

namespace App\Tests\Command;

use App\Tests\AbstractServiceTest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCommandTest extends AbstractServiceTest
{
    /** @var CommandTester */
    protected $commandTester;

    /** @var Application */
    protected $application;

    /** @var Command */
    protected $command;


    protected function setUp()
    {
        parent::setUp();

        $this->application = new Application(static::$kernel);

        $this->command = $this->application->find($this->getCommandName());
        $this->commandTester = new CommandTester($this->command);

        $this->initServices();
        $this->destroyData();
        $this->initTestData();
    }


    protected function tearDown()
    {
        $this->destroyData();
        parent::tearDown();
    }


    abstract protected function getCommandName() : string;


    abstract protected function initServices() : void;


    /**
     * @throws \Exception
     */
    abstract protected function initTestData() : void;


    /**
     * @throws \Exception
     */
    abstract protected function destroyData() : void;

}
