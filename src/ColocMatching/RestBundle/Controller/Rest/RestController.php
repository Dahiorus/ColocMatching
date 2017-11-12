<?php

namespace ColocMatching\RestBundle\Controller\Rest;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\Visit\Visitable;
use ColocMatching\CoreBundle\Event\VisitEvent;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
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
 */
abstract class RestController extends Controller {

    /**
     * Extracts the User from the authentication token in the request
     *
     * @param Request $request
     *
     * @return User
     * @throws JWTDecodeFailureException
     * @throws UserNotFoundException
     */
    protected function extractUser(Request $request = null) {
        if (empty($request)) {
            $request = $this->get("request_stack")->getCurrentRequest();
        }

        /** @var string */
        $token = $this->get("lexik_jwt_authentication.extractor.authorization_header_extractor")->extract(
            $request);

        if (!empty($token)) {
            /** @var array */
            $payload = $this->get("lexik_jwt_authentication.encoder")->decode($token);

            return $this->get("coloc_matching.core.user_manager")->findByUsername($payload["username"]);
        }

        return null;
    }


    /**
     * Extracts pagination parameters from the parameter fetcher
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return array
     */
    protected function extractPageableParameters(ParamFetcher $paramFetcher) : array {
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
    protected function buildJsonResponse($content, int $statusCode = 200, array $headers = array ()) {
        /** @var SerializationContext $context */
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        return new JsonResponse($this->get("jms_serializer")->serialize($content, "json", $context),
            $statusCode, $headers, true);
    }


    /**
     * Creates a new visit on Read request call
     *
     * @param Visitable $visited The visited entity
     */
    protected function registerVisit(Visitable $visited) {
        $request = $this->get("request_stack")->getCurrentRequest();
        $visitor = $this->extractUser($request);

        if (empty($visitor)) {
            return;
        }

        $event = new VisitEvent($visited, $visitor);

        if ($visited instanceof Announcement) {
            $this->get("event_dispatcher")->dispatch(VisitEvent::ANNOUNCEMENT_VISITED, $event);
        }
        else if ($visited instanceof Group) {
            $this->get("event_dispatcher")->dispatch(VisitEvent::GROUP_VISITED, $event);
        }
        else if ($visited instanceof User) {
            $this->get("event_dispatcher")->dispatch(VisitEvent::USER_VISITED, $event);
        }
    }

}
