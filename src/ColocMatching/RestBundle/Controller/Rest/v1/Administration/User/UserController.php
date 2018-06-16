<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Administration\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Form\Type\User\UserDtoForm;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Validator\FormValidator;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * User administration controller
 *
 * @author Dahiorus
 *
 * @Rest\Route(path="/users", service="coloc_matching.rest.admin.user_announcement_controller")
 */
class UserController extends AbstractRestController
{
    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var FormValidator */
    private $formValidator;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserDtoManagerInterface $userManager,
        FormValidator $formValidator)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->userManager = $userManager;
        $this->formValidator = $formValidator;
    }


    /**
     * Updates an existing user
     *
     * @Rest\Put("/{id}", name="rest_admin_update_user", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(name="user", in="body", required=true, description="User to update",
     *     @Model(type=UserDtoForm::class)),
     *   @SWG\Response(response=200, description="User updated", @Model(type=UserDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=400, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws EntityNotFoundException
     */
    public function updateUserAction(int $id, Request $request)
    {
        $this->logger->debug("Putting an existing user", array ("id" => $id, "putParams" => $request->request->all()));

        return $this->handleUpdateUserRequest($id, $request, true);
    }


    /**
     * Updates (partial) an existing user
     *
     * @Rest\Patch("/{id}", name="rest_admin_patch_user", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(name="user", in="body", required=true, description="User to update",
     *     @Model(type=UserDtoForm::class)),
     *   @SWG\Response(response=200, description="User updated", @Model(type=UserDto::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="No user found"),
     *   @SWG\Response(response=400, description="Validation error")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws EntityNotFoundException
     */
    public function patchUserAction(int $id, Request $request)
    {
        $this->logger->debug("Patching an existing user",
            array ("id" => $id, "patchParams" => $request->request->all()));

        return $this->handleUpdateUserRequest($id, $request, false);
    }


    /**
     * Deletes an existing user
     *
     * @Rest\Delete("/{id}", name="rest_admin_delete_user", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(response=200, description="User deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteUserAction(int $id)
    {
        $this->logger->debug("Deleting an existing user", array ("id" => $id));

        try
        {
            /** @var UserDto $user */
            $user = $this->userManager->read($id);

            if (!empty($user))
            {
                $this->logger->info("User found", array ("user" => $user));

                $this->userManager->delete($user);

                $this->logger->info("User deleted", array ("user" => $user));
            }
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->warning("Trying to delete an non existing user", array ("id" => $id));
        }

        return new JsonResponse("User deleted");
    }


    /**
     * Updates an existing user status
     *
     * @Rest\Patch("/{id}/status", name="rest_admin_patch_user_status", requirements={"id"="\d+"})
     *
     * @Operation(tags={ "User" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(
     *     name="status", required=true, in="body",
     *     @SWG\Schema(required={ "value" },
     *       @SWG\Property(property="value", type="string", description="The value of the status",
     *         enum={"enabled", "vacation", "banned"}, default="enabled"))),
     *   @SWG\Response(response=200, description="User status updated", @Model(type=UserDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidParameterException
     * @throws ORMException
     */
    public function updateStatusAction(int $id, Request $request)
    {
        $this->logger->debug("Changing the status of a user",
            array ("id" => $id, "patchParams" => $request->request->all()));

        /** @var string $status */
        $status = $request->request->getAlpha("value");
        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $user = $this->userManager->updateStatus($user, $status);

        $this->logger->info("User status updated", array ("response" => $user));

        return $this->buildJsonResponse($user);
    }


    /**
     * Handles the update operation of the user
     *
     * @param int $id The user identifier
     * @param Request $request The current request
     * @param bool $fullUpdate If the operation is a patch or a full update
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     */
    private function handleUpdateUserRequest(int $id, Request $request, bool $fullUpdate)
    {
        /** @var UserDto $user */
        $user = $this->userManager->read($id);
        $user = $this->userManager->update($user, $request->request->all(), $fullUpdate);

        $this->logger->info("User updated", array ("response" => $user));

        return $this->buildJsonResponse($user);
    }

}