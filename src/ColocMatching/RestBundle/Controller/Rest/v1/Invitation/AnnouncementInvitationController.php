<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\DTO\Invitation\InvitationDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Form\Type\Invitation\InvitationDtoForm;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationDtoManagerInterface;
use ColocMatching\CoreBundle\Security\User\TokenEncoderInterface;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for resources /announcements/{id}/invitations
 *
 * @Rest\Route("/announcements/{id}/invitations", requirements={ "id": "\d+", "invitationId": "\d+" },
 *   service="coloc_matching.rest.announcement_invitation_controller")
 * @Security(expression="has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class AnnouncementInvitationController extends InvitableInvitationController
{
    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, InvitationDtoManagerInterface $inviationManager,
        AnnouncementDtoManagerInterface $invitableManager, TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker, $inviationManager, $invitableManager,
            $tokenEncoder);
    }


    /**
     * Lists an announcement invitations
     *
     * @Rest\Get(name="rest_get_announcement_invitations")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="10")
     * @Rest\QueryParam(name="sorts", nullable=true, description="Sorting parameters", default="createdAt")
     *
     * @Operation(tags={ "Invitation" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Response(response=200, description="Invitation found"),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No announcement found")
     * )
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
        return parent::getInvitationsAction($id, $paramFetcher);
    }


    /**
     * Creates an invitation on an announcement
     *
     * @Rest\Post(name="rest_create_announcement_invitation")
     * @Security(expression="has_role('ROLE_SEARCH')")
     *
     * @Operation(tags={ "Invitation" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The announcement identifier"),
     *   @SWG\Parameter(in="body", name="invitation", required=true, description="The invitation to create",
     *     @Model(type=InvitationDtoForm::class)),
     *   @SWG\Response(response=201, description="Invitation created", @Model(type=InvitationDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No announcement found"),
     *   @SWG\Response(response=422, description="Validation error")
     * )
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
        return parent::createInvitationAction($id, $request);
    }


    protected function getListRoute() : string
    {
        return "rest_get_announcement_invitations";
    }

}
