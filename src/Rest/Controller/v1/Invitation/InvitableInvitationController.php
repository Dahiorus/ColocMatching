<?php

namespace App\Rest\Controller\v1\Invitation;

use App\Core\DTO\Invitation\InvitableDto;
use App\Core\DTO\Invitation\InvitationDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Manager\DtoManagerInterface;
use App\Core\Manager\Invitation\InvitationDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Security\User\TokenEncoderInterface;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Event\Events;
use App\Rest\Event\InvitationCreatedEvent;
use App\Rest\Security\Authorization\Voter\InvitationVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, InvitationDtoManagerInterface $inviationManager,
        DtoManagerInterface $invitableManager, TokenEncoderInterface $tokenEncoder,
        EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->invitationManager = $inviationManager;
        $this->invitableManager = $invitableManager;
        $this->tokenEncoder = $tokenEncoder;
        $this->eventDispatcher = $eventDispatcher;
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

        $this->logger->debug("Listing an invitable invitations", array_merge(array ("id" => $id), $parameters));

        $pageable = PageRequest::create($parameters);
        /** @var InvitableDto $invitable */
        $invitable = $this->invitableManager->read($id);

        $this->evaluateUserAccess(InvitationVoter::LIST, $invitable);

        $response = new PageResponse($this->invitationManager->listByInvitable($invitable, $pageable),
            $this->getListRoute(), array_merge(array ("id" => $id), $parameters));

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
        $this->logger->debug("Posting a new invitation on an invitable",
            array ("id" => $id, "request" => $request->request));

        /** @var UserDto $user */
        $user = $this->tokenEncoder->decode($request);
        /** @var InvitableDto $invitable */
        $invitable = $this->invitableManager->read($id);

        $this->evaluateUserAccess(InvitationVoter::INVITE, $invitable);

        /** @var InvitationDto $invitation */
        $invitation = $this->invitationManager->create($invitable, $user, Invitation::SOURCE_SEARCH,
            $request->request->all());

        $this->eventDispatcher->dispatch(Events::INVITATION_CREATED_EVENT, new InvitationCreatedEvent($invitation));

        $this->logger->info("Invitation created", array ("response" => $invitation));

        return $this->buildJsonResponse($invitation, Response::HTTP_CREATED);
    }


    /**
     * Gets the list action route name
     * @return string
     */
    abstract protected function getListRoute() : string;

}
