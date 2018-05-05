<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\User\UserTokenDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Entity\User\UserToken;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserTokenDtoManagerInterface;
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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Rest\Route(path="/registrations", service="coloc_matching.rest.registration_controller")
 *
 * @author Dahiorus
 */
class RegistrationController extends AbstractRestController
{
    /** @var UserTokenDtoManagerInterface */
    private $userTokenManager;

    /** @var UserDtoManagerInterface */
    private $userManager;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, UserTokenDtoManagerInterface $userTokenManager,
        UserDtoManagerInterface $userManager)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->userTokenManager = $userTokenManager;
        $this->userManager = $userManager;
    }


    /**
     * Confirms a user registration
     *
     * @Rest\Post(path="/confirmation")
     *
     * @Operation(tags={ "Registration" },
     *   @SWG\Parameter(
     *     name="token", required=true, in="body",
     *     @SWG\Schema(
     *       @SWG\Property(property="value", type="string", description="The token value", required={ "value" }))
     *   ),
     *   @SWG\Response(response=200, description="User registration confirmed", @Model(type=UserDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidParameterException
     * @throws ORMException
     */
    public function confirmAction(Request $request)
    {
        $this->logger->info("Confirming a user registration", array ("postParams" => $request->request->all()));

        $userToken = $this->getUserToken($request);
        $user = $this->userManager->findByUsername($userToken->getUsername());
        $user = $this->userManager->updateStatus($user, UserConstants::STATUS_ENABLED, false);

        $this->userTokenManager->delete($userToken);

        $this->logger->info("User registration confirmed", array ("user" => $user));

        return $this->buildJsonResponse($user);
    }


    /**
     * Gets a UserToken from the specified request
     *
     * @param Request $request The request
     *
     * @return UserTokenDto
     */
    private function getUserToken(Request $request) : UserTokenDto
    {
        $tokenValue = $request->request->get("value");

        if (empty($tokenValue))
        {
            throw new BadRequestHttpException("Empty token value");
        }

        try
        {
            $userToken = $this->userTokenManager->findByToken($tokenValue);
        }
        catch (EntityNotFoundException $e)
        {
            throw new BadRequestHttpException("Unknown user token '$tokenValue'", $e);
        }

        if ($userToken->getReason() != UserToken::REGISTRATION_CONFIRMATION)
        {
            throw new BadRequestHttpException("Invalid user token '$tokenValue'");
        }

        return $userToken;
    }

}
