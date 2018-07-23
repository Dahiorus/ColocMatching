<?php

namespace ColocMatching\RestBundle\Security\OAuth;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @author Dahiorus
 */
class OAuthConnectRegistry implements ContainerAwareInterface
{
    private const SERVICE_PREFIX = "coloc_matching.rest.oauth_connect.";

    /**
     * @var ContainerInterface
     */
    private $container;


    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


    /**
     * Gets the service associated to the provider name
     *
     * @param string $providerName
     *
     * @return OAuthConnect
     */
    public function get(string $providerName) : OAuthConnect
    {
        $serviceName = self::SERVICE_PREFIX . strtolower($providerName);

        if (!$this->container->has($serviceName))
        {
            throw new \InvalidArgumentException(
                sprintf("No OAuth connect service for the provider '%s'", $providerName)
            );
        }

        /** @var OAuthConnect $service */
        $service = $this->container->get($serviceName);

        return $service;
    }

}
