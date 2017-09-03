<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\User\SelfControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resources /me
 *
 * @Rest\Route("/me")
 * @Security(expression="has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class SelfController extends RestController implements SelfControllerInterface {

    /**
     * Gets the authenticated user
     *
     * @Rest\Get(path="", name="rest_get_me")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getSelfAction(Request $request) {
        $this->get("logger")->info("Getting the authenticated user");

        /** @var User $user */
        $user = $this->extractUser($request);
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($user);

        $this->get("logger")->info("User found", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Updates the authenticated user
     *
     * @Rest\Put(path="", name="rest_update_me")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateSelfAction(Request $request) {
        $this->get("logger")->info("Updating the authenticated user");

        return $this->handleUpdateRequest($request, true);
    }


    /**
     * Updates (partial) the authenticated user
     *
     * @Rest\Patch(path="", name="rest_patch_me")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function patchSelfAction(Request $request) {
        $this->get("logger")->info("Patching the authenticated user");

        return $this->handleUpdateRequest($request, false);
    }


    private function handleUpdateRequest(Request $request, bool $fullUpdate) {
        try {
            /** @var User $user */
            $user = $this->get("coloc_matching.core.user_manager")->update($this->extractUser($request),
                $request->request->all(), $fullUpdate);
            /** @var EntityResponse $response */
            $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($user);

            $this->get("logger")->info("User updated", array ("response" => $response));

            return $this->buildJsonResponse($response, Response::HTTP_OK);
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while trying to update a the authenticated user",
                array ("request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }

}