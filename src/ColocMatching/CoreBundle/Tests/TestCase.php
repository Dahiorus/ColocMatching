<?php

namespace ColocMatching\CoreBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Base class for all tests needing data fixtures
 *
 * @author Dahiorus
 */
class TestCase extends KernelTestCase {


    public static function setUpBeforeClass() {
        self::bootKernel();
    }


    public static function tearDownAfterClass() {
        self::ensureKernelShutdown();
    }


    protected static function getRepository(string $entityClass) {
        return self::getContainer()->get("doctrine.orm.entity_manager")->getRepository($entityClass);
    }


    protected static function getForm(string $formClass) {
        return self::getContainer()->get("form.factory")->create($formClass);
    }


    protected static function getContainer(): ContainerInterface {
        return self::$kernel->getContainer();
    }


    protected static function createTempFile(string $filepath, string $filename): File {
        $file = tempnam(sys_get_temp_dir(), "tst");
        imagejpeg(imagecreatefromjpeg($filepath), $file);

        return new UploadedFile($file, $filename, "image/jpeg", null, null, true);
    }

}