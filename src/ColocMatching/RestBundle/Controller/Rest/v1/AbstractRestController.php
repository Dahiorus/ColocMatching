<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1;

use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Base controller class to extends
 * @author Dahiorus
 */
abstract class AbstractRestController
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var SerializerInterface */
    protected $serializer;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer)
    {
        $this->logger = $logger;
        $this->serializer = $serializer;
    }


    /**
     * Extracts pagination parameters from the parameter fetcher
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return array
     */
    protected function extractPageableParameters(ParamFetcher $paramFetcher) : array
    {
        $page = $paramFetcher->get("page", true);
        $size = $paramFetcher->get("size", true);
        $order = $paramFetcher->get("order", true);
        $sort = $paramFetcher->get("sort", true);

        return array ("page" => $page, "size" => $size, "order" => $order, "sort" => $sort);
    }


    /**
     * Builds a JsonResponse
     *
     * @param mixed $content The response content
     * @param int $statusCode The response status code
     * @param array $headers The response HTTP headers
     *
     * @return JsonResponse
     */
    protected function buildJsonResponse($content, int $statusCode = 200, array $headers = array ())
    {
        /** @var SerializationContext $context */
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        return new JsonResponse($this->serializer->serialize($content, "json", $context),
            $statusCode, $headers, true);
    }


    /**
     * Evaluates the access expression and throws an access denied exception if the result is false
     *
     * @param bool $accessExpression The expression to evaluate
     * @param string $exceptionMessage [optional] The exception message
     */
    protected function evaluateUserAccess(bool $accessExpression, string $exceptionMessage = "Access denied") : void
    {
        if (!$accessExpression)
        {
            throw new AccessDeniedException($exceptionMessage);
        }
    }
}