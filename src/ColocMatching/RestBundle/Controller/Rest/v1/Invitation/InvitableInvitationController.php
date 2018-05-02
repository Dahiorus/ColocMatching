<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\DTO\Invitation\InvitableDto;
use ColocMatching\CoreBundle\DTO\Invitation\InvitationDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Manager\DtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\PageRequest;
use ColocMatching\CoreBundle\Security\User\TokenEncoderInterface;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Security\Authorization\Voter\InvitationVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class InvitableInvitationController extends AbstractRestController
{
    /** @var InvitationDtoManagerInterface */
    protected $invitationManager;

    /** @var DtoManagerInterface */
    protected $invitableManager;

    /** @var TokenEncoderInterface */
    protected $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, InvitationDtoManagerInterface $inviationManager,
        DtoManagerInterface $invitableManager, TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->invitationManager = $inviationManager;
        $this->invitableManager = $invitableManager;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Lists an invitable invitations
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getInvitationsAction(int $id, ParamFetcher $paramFetcher)
    {
        $parameters = $this->extractPageableParameters($paramFetcher);

        $this->logger->info("Listing an invitable invitations", array_merge(array ("id" => $id), $parameters));

        $pageable = PageRequest::create($parameters);
        /** @var InvitableDto $invitable */
        $invitable = $this->invitableManager->read($id);

        $this->evaluateUserAccess(InvitationVoter::LIST, $invitable);

        $response = new PageResponse(
            $this->invitationManager->listByInvitable($invitable, $pageable),
            $this->getListRoute(), array_merge(array ("id" => $id), $parameters),
            $pageable, $this->invitationManager->countByInvitable($invitable));

        $this->logger->info("Listing an invitable invitations - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Creates an invitation on an invitable
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws InvalidFormException
     * @throws InvalidParameterException
     */
    public function createInvitationAction(int $id, Request $request)
    {
        $this->logger->info("Posting a new invitation on an announcement",
            array ("id" => $id, "request" => $request->request));

        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);
        /** @var InvitableDto $invitable */
        $invitable = $this->invitableManager->read($id);

        $this->evaluateUserAccess(InvitationVoter::INVITE, $invitable);

        /** @var InvitationDto $invitation */
        $invitation = $this->invitationManager->create($invitable, $user, Invitation::SOURCE_SEARCH,
            $request->request->all());

        $this->logger->info("Invitation created", array ("response" => $invitation));

        return $this->buildJsonResponse($invitation, Response::HTTP_CREATED);
    }


    /**
     * Gets the list action route name
     * @return string
     */
    abstract protected function getListRoute() : string;

}
