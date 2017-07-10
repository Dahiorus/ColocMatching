<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Housing;

class HousingMock {


    public static function createHousing(int $id, ?string $type, int $roomCount = 0, int $bedroomCount = 0,
        int $bathroomCount = 0, int $surfaceArea = 0, int $roomMateCount = 0) : Housing {
        $housing = new Housing();

        $housing->setId($id);
        $housing->setType($type);
        $housing->setRoomCount($roomCount);
        $housing->setBedroomCount($bedroomCount);
        $housing->setBathroomCount($bathroomCount);
        $housing->setSurfaceArea($surfaceArea);
        $housing->setRoomMateCount($roomMateCount);

        return $housing;
    }


    private function __construct() {
        // empty constructor
    }

}