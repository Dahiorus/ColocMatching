<?php

namespace ColocMatching\CoreBundle\Exception;

class BadParameterException extends ColocMatchingException {

    /**
     * @var string
     */
    private $parameterName;

    /**
     * @var mixed
     */
    private $parameterValue;


    /**
     * BadParameterException constructor.
     *
     * @param string $parameterName The name of the parameter on error
     * @param mixed $parameterValue The value of the parameter
     * @param string|null $message  The error message
     * @param int $code             The exception code
     */
    public function __construct(string $parameterName, $parameterValue, string $message = null, int $code = 0) {
        parent::__construct($message, $code);

        $this->parameterName = $parameterName;
        $this->parameterValue = $parameterValue;
    }


    public function getParameterName() : string {
        return $this->parameterName;
    }


    public function getParameterValue() {
        return $this->parameterValue;
    }


    public function toArray() : array {
        return array (
            "message" => $this->message,
            "code" => $this->code,
            "errors" => array (
                "parameterName" => $this->parameterName,
                "parameterValue" => $this->parameterValue
            )
        );
    }

}