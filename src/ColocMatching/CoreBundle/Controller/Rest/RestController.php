<?php

namespace ColocMatching\CoreBundle\Controller;

use ColocMatching\CoreBundle\Controller\Response\AbstractResponse;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Event\VisitEvent;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Repository\Filter\VisitFilter;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializationContext;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestController extends Controller {

    /**
     * Extracts the User from the authentication token in the request
     *
     * @param Request $request
     *
     * @return User
     * @throws JWTDecodeFailureException
     * @throws UserNotFoundException
     */
    public function extractUser(Request $request) {
        /** @var string */
        $token = $this->get("lexik_jwt_authentication.extractor.authorization_header_extractor")->extract(
            $request);
        /** @var array */
        $payload = $this->get("lexik_jwt_authentication.encoder")->decode($token);

        return $this->get("coloc_matching.core.user_manager")->findByUsername($payload["username"]);
    }


    /**
     * Extracts pagination parameters from the parameter fetcher
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return array
     */
    public function extractPageableParameters(ParamFetcher $paramFetcher) : array {
        $page = $paramFetcher->get("page", true);
        $limit = $paramFetcher->get("size", true);
        $order = $paramFetcher->get("order", true);
        $sort = $paramFetcher->get("sort", true);

        return array ("page" => $page, "limit" => $limit, "order" => $order, "sort" => $sort);
    }


    /**
     * Builds a JsonResponse from an AbstractResponse
     *
     * @param AbstractResponse $response
     * @param int $statusCode
     * @param array $headers
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function buildJsonResponse(AbstractResponse $response, int $statusCode, array $headers = array ()) {
        /** @var SerializationContext */
        $context = new SerializationContext();

        $context->setSerializeNull(true);

        return new JsonResponse($this->get("jms_serializer")->serialize($response, "json", $context),
            $statusCode, $headers, true);
    }


    /**
     * Builds a JsonResponse with status code 400
     *
     * @param InvalidFormDataException $exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function buildBadRequestResponse(InvalidFormDataException $exception) {
        return new JsonResponse($exception->toJSON(), Response::HTTP_BAD_REQUEST, array (), true);
    }


    /**
     * Creates a new visit on Read request call
     *
     * @param Visitable $visited The visited entity
     */
    public function registerVisit(Visitable $visited) {
        $request = $this->get("request_stack")->getCurrentRequest();
        $visitor = $this->extractUser($request);

        $filter = new VisitFilter();
        $filter->setVisited($visited);
        $filter->setVisitor($visitor);
        $filter->setVisitedAtSince(new \DateTime("2 minutes ago"));

        /** @var array<Visit> */
        $visits = $this->get("coloc_matching.core.visit_manager")->search($filter);

        if (empty($visits)) {
            $this->get("event_dispatcher")->dispatch(VisitEvent::LOADED,
                new VisitEvent($visited, $visitor));
        }
    }

}
