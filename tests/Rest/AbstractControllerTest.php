<?php

namespace App\Tests\Rest;

use App\Core\DTO\User\UserDto;
use App\Core\Security\User\TokenEncoderInterface;
use App\Tests\CreateUserTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractControllerTest extends WebTestCase
{
    use CreateUserTrait;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Client
     */
    protected static $client;

    /** @var EntityManagerInterface */
    private $entityManager;


    /**
     * @beforeClass
     */
    public static function setUpBeforeClass()
    {
        ini_set("memory_limit", "2G");
        static::bootKernel();
    }


    /**
     * @afterClass
     */
    public static function tearDownAfterClass()
    {
        static::ensureKernelShutdown();
        static::$client = null;
    }


    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        $this->logger = self::getService("logger");
        $this->entityManager = self::getService("doctrine.orm.entity_manager");

        $this->logger->warning(sprintf("----------------------  Starting test - [ %s :: %s ] -  ----------------------",
            get_class($this), $this->getName()));

        $this->initServices();
        $this->clearData();
        $this->initTestData();
        $this->entityManager->clear();
    }


    /**
     * @throws \Exception
     */
    protected function tearDown()
    {
        $this->clearData();
        $this->entityManager->clear();

        $this->logger->warning(sprintf("----------------------  Test ended - [ %s :: %s ] -  ----------------------",
            get_class($this), $this->getName()));

        static::$client = null;

        $this->entityManager->close();
        $this->entityManager = null;

        gc_collect_cycles();
    }


    /**
     * Initializes the client for the test
     *
     * @param array $options The client options
     * @param array $servers The server parameters
     *
     * @return Client
     */
    protected static function initClient(array $options = array (), array $servers = array ()) : Client
    {
        $client = static::createClient($options, $servers);
        $client->setServerParameter("HTTP_HOST", "coloc-matching.api");

        return $client;
    }


    /**
     * Creates a web client with a authenticated user
     *
     * @param UserDto $user The user to authenticate in the client
     * @param array $options The client options
     * @param array $servers The server parameters
     *
     * @return Client
     */
    protected static function createAuthenticatedClient(UserDto $user, array $options = array (),
        array $servers = array ()) : Client
    {
        $client = static::initClient($options, $servers);

        /** @var TokenEncoderInterface $tokenEncoder */
        $tokenEncoder = self::getService("coloc_matching.core.jwt_token_encoder");
        $token = $tokenEncoder->encode($user);

        $client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $token));

        return $client;
    }


    /**
     * Gets a service component corresponding to the identifier
     *
     * @param string $serviceId The service unique identifier
     *
     * @return mixed The service
     * @throws ServiceNotFoundException
     */
    protected static function getService(string $serviceId)
    {
        return static::$container->get($serviceId);
    }


    /**
     * Gets the response content in array of type objects. Can return null.
     *
     * @return mixed|null
     */
    protected function getResponseContent()
    {
        $response = static::$client->getResponse();
        $content = $response->getContent();

        if (empty($content))
        {
            return null;
        }

        return json_decode($content, true);
    }


    /**
     * Asserts status code returned by the client
     *
     * @param int $statusCode The status code to assert
     */
    protected static function assertStatusCode(int $statusCode) : void
    {
        $response = static::$client->getResponse();
        self::assertEquals($statusCode, $response->getStatusCode(),
            "Expected status code to be $statusCode instead of " . $response->getStatusCode());
    }


    /**
     * Asserts the response has a header named Location
     */
    protected static function assertHasLocation()
    {
        $location = static::$client->getResponse()->headers->get("Location");
        static::assertNotNull($location, "Expected response to have header 'Location'");
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

        return new UploadedFile($file, $filename, "image/jpeg", null, true);
    }


    /**
     * Called before each test to initialize all needed services
     */
    abstract protected function initServices() : void;


    /**
     * Called before each test to initialize test data
     *
     * @throws \Exception
     */
    abstract protected function initTestData() : void;


    /**
     * Called before and after each test to clear data
     *
     * @throws \Exception
     */
    abstract protected function clearData() : void;

}
