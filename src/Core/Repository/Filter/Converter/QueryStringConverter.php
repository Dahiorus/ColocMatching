<?php

namespace App\Core\Repository\Filter\Converter;

use App\Core\Exception\UnsupportedSerializationException;
use App\Core\Repository\Filter\Searchable;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
use RuntimeException;

class QueryStringConverter implements StringConverterInterface
{
    const PARAM_SEPARATOR = ",";

    /** @var ArrayTransformerInterface */
    private $arrayTransformer;


    public function __construct(ArrayTransformerInterface $arrayTransformer)
    {
        $this->arrayTransformer = $arrayTransformer;
    }


    public function toString(Searchable $searchable) : string
    {
        $context = SerializationContext::create()->setSerializeNull(false);
        $array = $this->arrayTransformer->toArray($searchable, $context);
        $array = array_filter($array, function ($value) {
            return !empty($value);
        });

        $queryString = http_build_query($array, null, self::PARAM_SEPARATOR);

        return urldecode(trim($queryString));
    }


    public function toObject(string $value, string $class) : Searchable
    {
        if (!is_subclass_of($class, Searchable::class))
        {
            throw new UnsupportedSerializationException($value);
        }

        $string = str_replace(self::PARAM_SEPARATOR, "&", $value);

        parse_str($string, $array);

        try
        {
            return $this->arrayTransformer->fromArray($array, $class);
        }
        catch (RuntimeException $e)
        {
            throw new UnsupportedSerializationException($value, $e);
        }
    }

}
