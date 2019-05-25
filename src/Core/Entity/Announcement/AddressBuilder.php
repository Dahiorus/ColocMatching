<?php

namespace App\Core\Entity\Announcement;

class AddressBuilder
{
    /** @var Address */
    private $address;


    public function __construct()
    {
        $this->address = new Address();
    }


    public function build() : Address
    {
        return $this->address;
    }


    public function streetNumber($streetNumber)
    {
        $this->address->setStreetNumber($streetNumber);

        return $this;
    }


    public function route($route)
    {
        $this->address->setRoute($route);

        return $this;
    }


    public function locality($locality)
    {
        $this->address->setLocality($locality);

        return $this;
    }


    public function country($country)
    {
        $this->address->setCountry($country);

        return $this;
    }


    public function zipCode($zipCode)
    {
        $this->address->setZipCode($zipCode);

        return $this;
    }


    public function lat($lat)
    {
        $this->address->setLat($lat);

        return $this;
    }


    public function lng($lng)
    {
        $this->address->setLng($lng);

        return $this;
    }

}
