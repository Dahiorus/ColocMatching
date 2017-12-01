<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Event\RegistrationEvent;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\RegistrationControllerInterface;
use Doctrine\ORM\OptimisticLockException;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST Controller to register a new user in the application
 *
 * @Rest\Route("/registrations")
 *
 * @author brondon.ung
 */
class RegistrationController extends RestController implements RegistrationControllerInterface {

    /**
     * @Rest\Post("", name="rest_register_user")
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws OptimisticLockException
     */
    public function registerAction(Request $request) {
        $this->get("logger")->info("Registering a new user", array ("request" => $request->request));

        /** @var User $user */
        $user = $this->get('coloc_matching.core.user_manager')->create($request->request->all(), false);
        $this->get("event_dispatcher")->dispatch(RegistrationEvent::REGISTERED_EVENT, new RegistrationEvent($user));
        $this->get("doctrine.orm.entity_manager")->flush(); // the flush must occur after the mail sending

        $this->get("logger")->info("User registered", array ("user" => $user));

        return $this->buildJsonResponse("Registration succeeded", Response::HTTP_CREATED);
    }
}