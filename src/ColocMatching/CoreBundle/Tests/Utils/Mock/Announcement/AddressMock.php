<?php

namespace ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement;

use ColocMatching\CoreBundle\Form\DataTransformer\AddressTypeToAddressTransformer;

class AddressMock {


    public static function createAddress(string $str) {
        $transformer = new AddressTypeToAddressTransformer();

        return $transformer->reverseTransform($str);
    }


    private function __construct() {
        // empty constructor
    }

}