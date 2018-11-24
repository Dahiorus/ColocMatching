<?php

namespace App\Tests\Core\Repository\Filter\Converter;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserGender;
use App\Core\Entity\User\UserStatus;
use App\Core\Exception\UnsupportedSerializationException;
use App\Core\Repository\Filter\Converter\Base64StringConverter;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\UserFilter;
use App\Tests\AbstractServiceTest;
use JMS\Serializer\ArrayTransformerInterface;

class Base64StringConverterTest extends AbstractServiceTest
{
    /** @var StringConverterInterface */
    private $stringConverter;


    protected function setUp()
    {
        parent::setUp();

        /** @var ArrayTransformerInterface $serializer */
        $serializer = $this->getService("jms_serializer");

        $this->stringConverter = new Base64StringConverter($serializer);
    }


    /**
     * @test
     */
    public function convertSearchableShouldReturnValidBase64String()
    {
        $filter = new UserFilter();
        $filter
            ->setTags(["one", "two", "three"])
            ->setGender(UserGender::FEMALE)
            ->setAgeStart(12)
            ->setCreatedAtSince(new \DateTime())
            ->setStatus([UserStatus::ENABLED]);

        $base64String = $this->stringConverter->toString($filter);

        self::assertNotNull($base64String, "Expected to get a string from the converter");
        self::assertTrue(base64_decode($base64String, true) != false,
            "Expected the result string to be a base 64 string");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function convertBase64StringToSearchableShouldReturnValidObject()
    {
        $data = base64_encode(json_encode(array (
            "tags" => ["one", "two"],
            "gender" => "male",
            "createdAtSince" => '2018-01-10T00:00:00',
        )));

        $filter = $this->stringConverter->toObject($data, UserFilter::class);

        self::assertNotNull($filter, "Expected to get a non null object");
        self::assertInstanceOf(UserFilter::class, $filter, "Expected to get a UserFilter");
    }


    /**
     * @test
     * @throws \Exception
     */
    public function convertNonBase64StringShouldThrowException()
    {
        $this->expectException(UnsupportedSerializationException::class);

        $this->stringConverter->toObject("&é&è_èé-(&ç_)", UserFilter::class);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function convertBase64StringToOtherObjectShouldThrowException()
    {
        $data = base64_encode(json_encode(array (
            "tags" => ["one", "two"],
            "gender" => "male",
            "createdAtSince" => '2018-01-10T00:00:00',
        )));

        $this->expectException(UnsupportedSerializationException::class);

        $this->stringConverter->toObject($data, UserDto::class);
    }

}