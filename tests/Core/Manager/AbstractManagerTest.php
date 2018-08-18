<?php

namespace App\Tests\Core\Manager;

use App\Core\DTO\AbstractDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Manager\DtoManagerInterface;
use App\Core\Mapper\DtoMapperInterface;
use App\Core\Validator\ValidationError;
use App\Tests\Core\AbstractServiceTest;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractManagerTest extends AbstractServiceTest
{
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


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->getService("doctrine.orm.entity_manager");
        $this->manager = $this->initManager();
        $this->testData = $this->initTestData();

        $this->cleanData();
        $this->testDto = $this->createAndAssertEntity();
    }


    protected function tearDown()
    {
        $this->cleanData();
        parent::tearDown();
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
     * @param string[] $fieldNames The field names having a validation error
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
