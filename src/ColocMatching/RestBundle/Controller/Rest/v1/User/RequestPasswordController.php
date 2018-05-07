<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Form\Type\Security\LostPasswordForm;
use ColocMatching\CoreBundle\Form\Type\User\PasswordRequestForm;
use ColocMatching\CoreBundle\Service\PasswordRequester;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Lost password requester
 *
 * @author Dahiorus
 *
 * @Rest\Route(path="/passwords", service="coloc_matching.rest.request_password_controller")
 */
class RequestPasswordController extends AbstractRestController
{
    /** @var PasswordRequester */
    private $passwordRequester;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, PasswordRequester $passwordRequester)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->passwordRequester = $passwordRequester;
    }


    /**
     * Requests a lost password to renew
     *
     * @Rest\Post(path="/request", name="rest_request_password")
     *
     * @Operation(tags={ "Lost password" },
     *   @SWG\Parameter(in="body", name="request", @Model(type=PasswordRequestForm::class)),
     *   @SWG\Response(response=201, description="Request created"),
     *   @SWG\Response(response=400, description="Unable to request password"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     */
    public function requestAction(Request $request)
    {
        $this->logger->info("Requesting a lost password", array ("postParams" => $request->request->all()));

        try
        {
            $this->passwordRequester->requestPassword($request->request->all());

            $this->logger->info("Lost password request sent");

            return $this->buildJsonResponse("Password request created", Response::HTTP_CREATED);
        }
        catch (EntityNotFoundException | InvalidParameterException $e)
        {
            $this->logger->error("Unexpected error while requesting a password",
                array ("postParams" => $request->request->all(), "exception" => $e));

            throw new BadRequestHttpException("Request password error", $e);
        }
    }


    /**
     * Posts a new password for the user linked to the user token
     *
     * @Rest\Post(name="rest_post_password")
     *
     * @Operation(tags={ "Lost password" },
     *   @SWG\Parameter(in="body", name="lostPassword", @Model(type=LostPasswordForm::class)),
     *   @SWG\Response(response=201, description="Password updated", @Model(type=UserDto::class)),
     *   @SWG\Response(response=400, description="Unable to update password"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function renewPasswordAction(Request $request)
    {
        $this->logger->info("Posting a new password");

        try
        {
            $user = $this->passwordRequester->updatePassword($request->request->all());

            $this->logger->info("User password renewed", array ("user" => $user));

            return $this->buildJsonResponse($user);
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->error("Unexpected error while updating a lost password", array ("exception" => $e));

            throw new BadRequestHttpException("Update lost password error", $e);
        }
    }

}
