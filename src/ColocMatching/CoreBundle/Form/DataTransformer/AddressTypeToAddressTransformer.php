<?php

namespace ColocMatching\CoreBundle\Form\DataTransformer;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\ProviderAggregator;
use Http\Adapter\Guzzle6\Client;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AddressTypeToAddressTransformer implements DataTransformerInterface
{
    /** @var string */
    private $region;

    /** @var string */
    private $apiKey;


    /**
     * AddressTypeToAddressTransformer constructor.
     *
     * @param string $region
     * @param string $apiKey
     */
    public function __construct(string $region, string $apiKey)
    {
        $this->region = $region;
        $this->apiKey = $apiKey;
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\DataTransformerInterface::transform()
     */
    public function transform($value)
    {
        /** @var Address $value */
        if (empty($value))
        {
            return "";
        }

        return $value->getFormattedAddress();
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\DataTransformerInterface::reverseTransform()
     */
    public function reverseTransform($value)
    {
        if (empty($value))
        {
            return null;
        }

        return $this->textToAddress($value);
    }


    private function textToAddress(string $text) : Address
    {
        /** @var ProviderAggregator */
        $geocoder = new ProviderAggregator();
        $geocoder->registerProvider(new GoogleMaps(new Client(), $this->region, $this->apiKey));

        /** @var AddressCollection */
        $collection = $geocoder->geocode($text);

        if (empty($collection))
        {
            throw new TransformationFailedException("No address found for '$text'");
        }

        /** @var \Geocoder\Model\Address $geocoded */
        $geocoded = $collection->first();
        $address = new Address();

        $address->setStreetNumber($geocoded->getStreetNumber());
        $address->setRoute($geocoded->getStreetName());
        $address->setLocality($geocoded->getLocality());
        $address->setZipCode($geocoded->getPostalCode());
        $address->setCountry($geocoded->getCountry()->getName());
        $address->setLat($geocoded->getCoordinates()->getLatitude());
        $address->setLng($geocoded->getCoordinates()->getLongitude());
        $address->buildFullAddress();

        return $address;
    }

}