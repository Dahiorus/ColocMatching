<?php

namespace ColocMatching\RestBundle\Controller\Rest;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Service\VisitorInterface;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializationContext;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract class for REST controllers
 *
 * @package ColocMatching\RestBundle\Controller
 * @deprecated
 */
abstract class RestController extends Controller
{

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
     * Builds a JsonResponse from an AbstractResponse
     *
     * @param mixed $content
     * @param int $statusCode
     * @param array $headers
     *
     * @return JsonResponse
     */
    protected function buildJsonResponse($content, int $statusCode = 200, array $headers = array ())
    {
        /** @var SerializationContext $context */
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        return new JsonResponse($this->get("jms_serializer")->serialize($content, "json", $context),
            $statusCode, $headers, true);
    }


    /**
     * Extracts the User from the authentication token in the request
     *
     * @param Request $request
     *
     * @return User
     * @throws JWTDecodeFailureException
     * @throws UserNotFoundException
     * @deprecated
     */
    protected function extractUser(Request $request = null)
    {
        if (empty($request))
        {
            $request = $this->get("request_stack")->getCurrentRequest();
        }

        return $this->get("coloc_matching.core.jwt_user_extractor")->getAuthenticatedUser($request);
    }


    /**
     * Calls the visitor to dispatch a visit event
     *
     * @param Visitable $visited The visited entity
     *
     * @deprecated
     */
    protected function registerVisit(Visitable $visited)
    {
        /** @var VisitorInterface $visitor */
        $visitor = $this->get("coloc_matching.core.event_dispatcher_visitor");

        $visited->accept($visitor);
    }

}
