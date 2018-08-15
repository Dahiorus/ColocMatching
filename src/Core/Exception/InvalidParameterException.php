<?php

namespace App\Core\Exception;

/**
 * Exception thrown when an invalid parameter is encountered.
 *
 * @author Dahiorus
 */
class InvalidParameterException extends ColocMatchingException
{

    /**
     * @var string
     */
    protected $parameterName;


    /**
     * InvalidParameterException constructor.
     *
     * @param string $parameterName The name of the parameter on error
     * @param string $message [optional] The error message
     */
    public function __construct(string $parameterName, string $message = "Invalid parameter")
    {
        parent::__construct($message, 400);

        $this->parameterName = $parameterName;
    }


    public function getParameterName() : string
    {
        return $this->parameterName;
    }


    public function getDetails() : array
    {
        $details = parent::getDetails();
        $details["errors"] = array ("parameter" => $this->parameterName);

        return $details;
    }

}