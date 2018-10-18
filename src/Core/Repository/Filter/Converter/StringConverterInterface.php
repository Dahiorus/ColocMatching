<?php

namespace App\Core\Repository\Filter\Converter;

use App\Core\Exception\UnsupportedSerializationException;
use App\Core\Repository\Filter\Searchable;

/**
 * String converter for Searchable
 *
 * @author Dahiorus
 */
interface StringConverterInterface
{
    /**
     * Converts the specified searchable to a string representation
     *
     * @param Searchable $searchable The object to convert
     *
     * @return string
     */
    public function toString(Searchable $searchable) : string;


    /**
     * Converts string value to an instance of the specified class
     *
     * @param string $value The base 64 string to convert
     * @param string $class The target class
     *
     * @return Searchable
     * @throws UnsupportedSerializationException
     */
    public function toObject(string $value, string $class) : Searchable;
}
