<?php

namespace App\Tests\Core\Repository\Filter\Converter;

use App\Core\Entity\Announcement\Address;
use App\Core\Entity\User\UserGender;
use App\Core\Exception\UnsupportedSerializationException;
use App\Core\Repository\Filter\AnnouncementFilter;
use App\Core\Repository\Filter\Converter\QueryStringConverter;
use App\Core\Repository\Filter\Converter\StringConverterInterface;
use App\Core\Repository\Filter\UserFilter;
use App\Tests\AbstractServiceTest;
use Exception;
use JMS\Serializer\ArrayTransformerInterface;

class QueryStringConverterTest extends AbstractServiceTest
{
    /** @var StringConverterInterface */
    private $stringConverter;


    protected function setUp()
    {
        parent::setUp();

        /** @var ArrayTransformerInterface $serializer */
        $serializer = $this->getService("jms_serializer");

        $this->stringConverter = new QueryStringConverter($serializer);
    }


    /**
     * @test
     * @throws Exception
     */
    public function convertSimpleSearchable()
    {
        $filter = new UserFilter();
        $filter
            ->setWithDescription(true)
            ->setGender(UserGender::FEMALE)
            ->setAgeStart(12);

        $queryString = $this->stringConverter->toString($filter);

        self::assertNotNull($queryString, "Expected to get a string from the converter");
        self::assertEquals(
            "gender:female,ageStart:12,withDescription:1",
            $queryString,
            "Expected to get a valid query string");
    }


    /**
     * @test
     * @throws Exception
     */
    public function convertSearchableWithArrayValue()
    {
        $filter = new UserFilter();
        $filter->setTags(["one", "two", "three"]);

        $queryString = $this->stringConverter->toString($filter);

        self::assertNotNull($queryString, "Expected to get a string from the converter");
        self::assertEquals(
            "tags[0]:one,tags[1]:two,tags[2]:three",
            $queryString,
            "Expected to get a valid query string");
    }


    /**
     * @test
     * @throws Exception
     */
    public function convertSearchableWithNestedObject()
    {
        $filter = new AnnouncementFilter();
        $address = new Address();
        $address->setCountry("France")
            ->setLocality("Paris");
        $filter->setAddress($address)
            ->setWithDescription(true)
            ->setBathroomCount(1)
            ->setRentPriceStart(520);

        $queryString = $this->stringConverter->toString($filter);

        self::assertNotNull($queryString, "Expected to get a string from the converter");
        self::assertEquals(
            "address[locality]:Paris,address[country]:France,rentPriceStart:520,withDescription:1,bathroomCount:1",
            $queryString,
            "Expected to get a valid query string");
    }


    /**
     * @test
     * @throws Exception
     */
    public function convertQueryStringToSearchable()
    {
        $string = "withDescription:true,ageStart:15,ageEnd:23,tags[]:tag1,tags[]:tag2";

        /** @var UserFilter $filter */
        $filter = $this->stringConverter->toObject($string, UserFilter::class);

        self::assertNotNull($filter, "Expected to get a non null object");
        self::assertInstanceOf(UserFilter::class, $filter, "Expected to get a UserFilter");

        self::assertTrue($filter->isWithDescription());
        self::assertEquals(15, $filter->getAgeStart());
        self::assertEquals(23, $filter->getAgeEnd());
        self::assertEquals(["tag1", "tag2"], $filter->getTags());
    }


    /**
     * @test
     * @throws Exception
     */
    public function convertToNonSearchableClassShouldThrowException()
    {
        $this->expectException(UnsupportedSerializationException::class);

        $this->stringConverter->toObject("qfdsflsdj", "qldjlsj");
    }

}
