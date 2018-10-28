<?php

namespace App\Rest\Controller\v1;

use App\Core\Repository\Filter\Pageable\Order;
use FOS\RestBundle\Request\ParamFetcher;
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
    protected const PAGE = "page";
    protected const SIZE = "size";
    protected const SORTS = "sorts";

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
        if (empty($paramFetcher->all(true)))
        {
            return array ();
        }

        $page = $paramFetcher->get(self::PAGE, true);
        $size = $paramFetcher->get(self::SIZE, true);
        $sorts = $paramFetcher->get(self::SORTS, true);

        /** @var string[] $sortElements */
        $sortElements = explode(",", $sorts);
        $sort = array ();

        foreach ($sortElements as $sortElement)
        {
            if (strpos($sortElement, "-") === 0)
            {
                $property = substr($sortElement, 1);
                $sort[ $property ] = Order::DESC;
            }
            else
            {
                $property = $sortElement;
                $sort[ $property ] = Order::ASC;
            }
        }

        return array (self::PAGE => $page, self::SIZE => $size, self::SORTS => $sort);
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
        return new JsonResponse($this->serializer->serialize($content, "json"),
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
