<?php

namespace App\Core\Validator;

/**
 * Represents a validation error on a object property
 *
 * @author Dahiorus
 */
class ValidationError
{

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var mixed
     */
    private $cause;


    public function __construct(string $propertyName, string $message, $cause)
    {
        $this->propertyName = $propertyName;
        $this->message = $message;
        $this->cause = $cause;
    }


    public function __toString()
    {
        return "ValidationError [propertyName=" . $this->propertyName . ", message=" . $this->message . ", cause="
            . $this->cause . "]";
    }


    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }


    /**
     * @return string
     */
    public function getPropertyName() : string
    {
        return $this->propertyName;
    }


    /**
     * @return mixed
     */
    public function getCause()
    {
        return $this->cause;
    }

}