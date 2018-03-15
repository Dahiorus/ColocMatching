<?php

namespace ColocMatching\CoreBundle\Tests\Manager;

use ColocMatching\CoreBundle\DTO\AbstractDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Manager\DtoManagerInterface;
use ColocMatching\CoreBundle\Mapper\DtoMapperInterface;
use ColocMatching\CoreBundle\Validator\ValidationError;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractManagerTest extends KernelTestCase
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var DtoMapperInterface */
    protected $dtoMapper;

    /** @var DtoManagerInterface */
    protected $manager;

    /** @var array */
    protected $testData;

    /** @var AbstractDto */
    protected $testDto;


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
        $this->em = $this->getService("doctrine.orm.entity_manager");
        $this->manager = $this->initManager();
        $this->testData = $this->initTestData();

        $this->cleanData();
        $this->logger->info("----------------------  Starting test  ----------------------",
            array ("test" => $this->getName()));

        $this->testDto = $this->createAndAssertEntity();
    }


    protected function tearDown()
    {
        $this->cleanData();
        $this->logger->info("----------------------  End test  ----------------------",
            array ("test" => $this->getName()));
    }


    /**
     * Gets the entity repository of an entity class
     *
     * @param string $entityClass The string representation of the entity class
     *
     * @return ObjectRepository The entity class repository
     */
    protected function getRepository(string $entityClass)
    {
        return $this->em->getRepository($entityClass);
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
        return self::$kernel->getContainer()->get($serviceId);
    }


    /**
     * Creates a temporary JPEG file from the file path and name
     *
     * @param string $filePath The file path
     * @param string $filename The file name
     *
     * @return UploadedFile
     */
    protected static function createTmpJpegFile(string $filePath, string $filename) : UploadedFile
    {
        $file = tempnam(sys_get_temp_dir(), "tst");
        imagejpeg(imagecreatefromjpeg($filePath), $file);

        return new UploadedFile($file, $filename, "image/jpeg", null, null, true);
    }


    /**
     * Asserts validation errors are found for the field names
     *
     * @param callable $execution The execution which throws an InvalidFormException
     * @param string[] ...$fieldNames The field names having a validation error
     */
    protected static function assertValidationError(callable $execution, string... $fieldNames)
    {
        try
        {
            $execution();
        }
        catch (\Exception $e)
        {
            if (!($e instanceof InvalidFormException))
            {
                self::fail("Expected an '" . InvalidFormException::class . "', but instead got '" . get_class($e) . "'");
            }

            /** @var ValidationError[] $errors */
            $errors = $e->getErrors();

            self::assertNotEmpty($errors, "Expected to find validation errors");

            /** @var string[] $errorFields */
            $errorFields = array_map(function ($error) {
                /** @var ValidationError $error */
                return $error->getPropertyName();
            }, $errors);

            foreach ($fieldNames as $fieldName)
            {
                self::assertContains($fieldName, $errorFields, "Expected to find a validation error on '$fieldName'");
            }
        }
    }


    /**
     * Initiates the CRUD manager
     * @return DtoManagerInterface An instance of the manager
     */
    abstract protected function initManager();


    /**
     * Initiates the test data
     *
     * @return array The test data
     */
    abstract protected function initTestData() : array;


    /**
     * Creates an entity from the test data
     *
     * @return AbstractDto
     * @throws \Exception
     */
    abstract protected function createAndAssertEntity();


    /**
     * Cleans all test data
     */
    abstract protected function cleanData() : void;


    /**
     * Asserts the entity data (can be overrode to assert other properties)
     *
     * @param AbstractDto $dto
     */
    protected function assertDto($dto) : void
    {
        self::assertNotNull($dto, "Expected DTO to be not null");
        self::assertNotEmpty($dto->getId(), "Expected DTO to have an identifier");
    }


    /**
     * @throws \Exception
     */
    public function testRead()
    {
        $dto = $this->manager->read($this->testDto->getId());

        $this->assertDto($dto);
    }


    /**
     * @throws \Exception
     */
    public function testReadNonExistingEntity()
    {
        $this->expectException(EntityNotFoundException::class);

        $this->manager->read(999);
    }


    /**
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->manager->delete($this->testDto);
        $this->expectException(EntityNotFoundException::class);
        $this->manager->read($this->testDto->getId());
    }

}