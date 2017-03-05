<?php

namespace ColocMatching\CoreBundle\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Base class for all tests needing data fixtures
 *
 * @author Dahiorus
 */
class TestCase extends KernelTestCase {

    /**
     * @var LoggerInterface
     */
    protected static $logger;

    /**
     * @var EntityManagerInterface
     */
    protected static $entityManager;


    public static function setUpBeforeClass() {
        self::bootKernel();

        self::$entityManager = self::getContainer()->get("doctrine.orm.entity_manager");
        self::$logger = self::getContainer()->get("logger");

        self::generateSchema();
        self::loadFixtures(self::$kernel, self::$entityManager);
    }


    public static function tearDownAfterClass() {
        self::ensureKernelShutdown();
        self::$entityManager = null;
        self::$logger = null;
    }


    protected static function getEntityManager(): EntityManagerInterface {
        return static::$entityManager;
    }


    protected static function generateSchema() {
        $metadata = self::getMetadata();

        if (empty($metadata)) {
            throw new SchemaException('No Metadata Classes to process.');
        }

        $tool = new SchemaTool(self::$entityManager);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }


    protected static function getMetadata(): array {
        return self::$entityManager->getMetadataFactory()->getAllMetadata();
    }


    protected static function getContainer(): ContainerInterface {
        return self::$kernel->getContainer();
    }


    protected function createTempFile(string $filepath, string $filename): File {
        $file = tempnam(sys_get_temp_dir(), "tst");
        imagejpeg(imagecreatefromjpeg($filepath), $file);

        return new UploadedFile($file, $filename, "image/jpeg", null, null, true);
    }


    private static function loadFixtures(Kernel $kernel, EntityManagerInterface $em) {
        self::$logger->debug("Load data fixtures");

        $loader = new Loader($kernel->getContainer());

        foreach ($kernel->getBundles() as $bundle) {
            $path = $bundle->getPath() . '/DataFixtures/ORM';

            try {
                self::$logger->debug("Loading fixtures from '$path'");

                $loader->loadFromDirectory($path);
            }
            catch (\InvalidArgumentException $e) {
                // do nothing
            }
        }

        $fixtures = $loader->getFixtures();

        if (empty($fixtures)) {
            throw new \InvalidArgumentException('Could not find any fixtures to load in');
        }

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($fixtures, true);
    }

}