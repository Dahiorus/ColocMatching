<?php

namespace ColocMatching\CoreBundle\Form\DataTransformer;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use Doctrine\Common\Collections\Collection;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\GoogleMaps;
use Geocoder\ProviderAggregator;
use Ivory\HttpAdapter\CurlHttpAdapter;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AddressTypeToAddressTransformer implements DataTransformerInterface {


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\DataTransformerInterface::transform()
     */
    public function transform($value) {
        if (empty($value)) {
            return "";
        }

        return $value->getFormattedAddress();
    }


    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Form\DataTransformerInterface::reverseTransform()
     */
    public function reverseTransform($value) {
        if (empty($value)) {
            return null;
        }

        return $this->textToAddress($value);
    }


    private function textToAddress(string $text) {
        /** @var Address */
        $address = null;

        /** @var ProviderAggregator */
        $geocoder = new ProviderAggregator(1);
        $geocoder->registerProvider(new GoogleMaps(new CurlHttpAdapter(), "fr"));

        /** @var AddressCollection */
        $collection = $geocoder->geocode($text);

        if (empty($collection)) {
            throw new TransformationFailedException("No address found for '$text'");
        }

        /** @var \Geocoder\Model\Address */
        $geocoded = $collection->first();
        $address = new Address();

        $address->setStreetNumber($geocoded->getStreetNumber());
        $address->setRoute($geocoded->getStreetName());
        $address->setLocality($geocoded->getLocality());
        $address->setZipCode($geocoded->getPostalCode());
        $address->setCountry($geocoded->getCountry());
        $address->setLat($geocoded->getLatitude());
        $address->setLng($geocoded->getLongitude());

        return $address;
    }

}