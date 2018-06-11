<?php

namespace ColocMatching\RestBundle\Exception;

use ColocMatching\CoreBundle\Exception\ColocMatchingException;

class OAuthConfigurationError extends ColocMatchingException
{
    /**
     * @var string
     */
    private $providerName;


    public function __construct(string $providerName, \Throwable $previous = null)
    {
        parent::__construct(sprintf("Invalid '%s' OAuth connect configuration", $providerName), 500, $previous);

        $this->providerName = $providerName;
    }


    /**
     * @return string
     */
    public function getProviderName() : string
    {
        return $this->providerName;
    }
}
