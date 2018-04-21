<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1;

use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->authorizationChecker = $authorizationChecker;
    }


    /**
     * Extracts the paging parameters from the parameter fetcher
     *
     * @param ParamFetcher $paramFetcher The parameter fetcher
     *
     * @return array
     */
    protected function extractPageableParameters(ParamFetcher $paramFetcher)
    {
        $page = $paramFetcher->get("page", true);
        $size = $paramFetcher->get("size", true);
        $sorts = $paramFetcher->get("sorts", true);

        $sorts = is_array($sorts) ? $sorts : array ($sorts);
        $sort = array ();
        foreach ($sorts as $value)
        {
            list($property, $direction) = explode(",", $value);
            $sort[ $property ] = $direction;
        }

        return array ("page" => $page, "size" => $size, "sort" => $sort);
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
    protected function buildJsonResponse($content, int $statusCode = Response::HTTP_OK, array $headers = array ())
    {
        /** @var SerializationContext $context */
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        return new JsonResponse($this->serializer->serialize($content, "json", $context),
            $statusCode, $headers, true);
    }


    /**
     * Throws an AccessDeniedException unless the attributes are granted against the current authentication token and
     * optionally supplied subject.
     *
     * @param mixed $attributes The attributes
     * @param mixed $subject [optional] The supplied object
     * @param string $exceptionMessage [optional] The exception message
     *
     * @throws AccessDeniedException
     */
    protected function evaluateUserAccess($attributes, $subject = null,
        string $exceptionMessage = "Access denied") : void
    {
        $hasAccess = $this->authorizationChecker->isGranted($attributes, $subject);

        if (!$hasAccess)
        {
            $exception = new AccessDeniedException($exceptionMessage);
            $exception->setAttributes($attributes);
            $exception->setSubject($subject);

            throw $exception;
        }
    }

}
