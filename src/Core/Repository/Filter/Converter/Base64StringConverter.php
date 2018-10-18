<?php

namespace App\Core\Repository\Filter\Converter;

use App\Core\Exception\UnsupportedSerializationException;
use App\Core\Repository\Filter\Searchable;
use JMS\Serializer\ArrayTransformerInterface;

/**
 * Base 64 string converter for Searchable
 *
 * @author Dahiorus
 */
class Base64StringConverter implements StringConverterInterface
{
    /**
     * @var ArrayTransformerInterface
     */
    private $serializer;


    public function __construct(ArrayTransformerInterface $serializer)
    {
        $this->serializer = $serializer;
    }


    public function toString(Searchable $searchable) : string
    {
        /** @var array $array */
        $array = $this->serializer->toArray($searchable);
        /** @var string $json */
        $json = json_encode($array);

        return base64_encode($json);
    }


    public function toObject(string $value, string $class) : Searchable
    {
        $json = base64_decode($value, true);

        if ($json == false || !is_subclass_of($class, Searchable::class))
        {
            throw new UnsupportedSerializationException($value);
        }

        /** @var array $array */
        $array = json_decode($json, true);

        if (!is_array($array))
        {
            throw new UnsupportedSerializationException($value);
        }

        try
        {
            return $this->serializer->fromArray($array, $class);
        }
        catch (\RuntimeException $e)
        {
            throw new UnsupportedSerializationException($value, $e);
        }
    }

}
