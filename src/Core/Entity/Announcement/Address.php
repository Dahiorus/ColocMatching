<?php

namespace App\Core\Entity\Announcement;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Address
 *
 * @ORM\Embeddable
 */
class Address
{
    /**
     * @var string
     *
     * @ORM\Column(name="street_number", type="string", length=10, nullable=true)
     * @Assert\Regex(pattern="/^\d+/")
     * @Serializer\Type("string")
     */
    private $streetNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=255, nullable=true)
     * @Serializer\Type("string")
     */
    private $route;

    /**
     * @var string
     *
     * @ORM\Column(name="locality", type="string", length=100, nullable=true)
     * @Serializer\Type("string")
     */
    private $locality;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=100, nullable=true)
     * @Assert\Regex(pattern="/^\p{L}+/")
     * @Serializer\Type("string")
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string", length=10, nullable=true)
     * @Assert\Regex(pattern="/^\d+/")
     * @Serializer\Type("string")
     */
    private $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(name="formatted_address", type="string", length=512, nullable=true)
     * @Serializer\Type("string")
     */
    private $formattedAddress;

    /**
     * @var double
     *
     * @ORM\Column(name="lat", type="decimal", precision=20, scale=14, nullable=true)
     * @Assert\Type(type="double")
     * @Serializer\Type("double")
     */
    private $lat;

    /**
     * @var double
     *
     * @ORM\Column(name="lng", type="decimal", precision=20, scale=14, nullable=true)
     * @Assert\Type(type="double")
     * @Serializer\Type("double")
     */
    private $lng;


    public function __toString()
    {
        return sprintf(
            "Address [streetNumber: '%s', route: '%s', locality: '%s', country: '%s', zipCode: '%s', formattedAddress: '%s', lat: %lf, lng: %lf]",
            $this->streetNumber, $this->route, $this->locality, $this->country, $this->zipCode, $this->formattedAddress,
            $this->lat, $this->lng);
    }


    /**
     * Set streetNumber
     *
     * @param string $streetNumber
     *
     * @return Address
     */
    public function setStreetNumber($streetNumber)
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }


    /**
     * Get streetNumber
     *
     * @return string
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }


    /**
     * Set route
     *
     * @param string $route
     *
     * @return Address
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }


    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }


    /**
     * Set locality
     *
     * @param string $locality
     *
     * @return Address
     */
    public function setLocality($locality)
    {
        $this->locality = $locality;

        return $this;
    }


    /**
     * Get locality
     *
     * @return string
     */
    public function getLocality()
    {
        return $this->locality;
    }


    /**
     * Set country
     *
     * @param string $country
     *
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }


    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }


    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return Address
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }


    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }


    /**
     * Set formattedAddress
     *
     * @param string $formattedAddress
     *
     * @return Address
     */
    public function setFormattedAddress($formattedAddress)
    {
        $this->formattedAddress = $formattedAddress;

        return $this;
    }


    /**
     * Get formattedAddress
     *
     * @return string
     */
    public function getFormattedAddress()
    {
        return $this->formattedAddress;
    }


    /**
     * Set lat
     *
     * @param double $lat
     *
     * @return Address
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }


    /**
     * Get lat
     *
     * @return double
     */
    public function getLat()
    {
        return $this->lat;
    }


    /**
     * Set lng
     *
     * @param double $lng
     *
     * @return Address
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }


    /**
     * Get lng
     *
     * @return double
     */
    public function getLng()
    {
        return $this->lng;
    }


    /**
     * Gets a short representation of this Address
     *
     * return string
     */
    public function getShortAddress()
    {
        if (!empty($this->zipCode))
        {
            return sprintf("%s %s", $this->locality, $this->zipCode);
        }

        return $this->locality;
    }


    public function buildFullAddress()
    {
        /** @var array */
        $components = [];

        if (!empty($this->streetNumber) && !empty($this->route))
        {
            $components[] = sprintf("%s %s", $this->streetNumber, $this->route);
        }
        else if (!empty($this->route))
        {
            $components[] = $this->route;
        }

        if (!empty($this->locality) && !empty($this->zipCode))
        {
            $components[] = sprintf("%s %s", $this->locality, $this->zipCode);
        }
        else if (!empty($this->locality))
        {
            $components[] = $this->locality;
        }
        else if (!empty($this->zipCode))
        {
            $components[] = $this->zipCode;
        }

        if (!empty($this->country))
        {
            $components[] = $this->country;
        }

        $this->setFormattedAddress(implode(", ", $components));
    }

}

