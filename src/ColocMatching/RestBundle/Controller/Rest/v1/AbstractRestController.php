<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * Creates a PageResponse from the content
     *
     * @param array $content The response content
     * @param int $total The total count of elements
     * @param PageableFilter $filter The query filter
     * @param Request $request The HTTP request
     *
     * @return PageResponse
     */
    protected function createPageResponse(array $content, int $total, PageableFilter $filter,
        Request $request) : PageResponse
    {
        $response = new PageResponse($content, $request->getUri());

        $response->setPage($filter->getPage());
        $response->setSize($filter->getSize());
        $response->setOrder($filter->getOrder());
        $response->setSort($filter->getSort());
        $response->setTotalElements($total);
        $response->setRelationLinks();

        return $response;
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