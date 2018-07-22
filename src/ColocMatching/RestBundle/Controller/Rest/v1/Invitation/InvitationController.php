<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\DTO\Invitation\InvitationDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationDtoManagerInterface;
use ColocMatching\RestBundle\Controller\Rest\v1\AbstractRestController;
use ColocMatching\RestBundle\Security\Authorization\Voter\InvitationVoter;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Rest\Route(path="/invitations/{id}", requirements={ "id": "\d+" },
 *   service="coloc_matching.rest.invitation_controller")
 * @Security("has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class InvitationController extends AbstractRestController
{
    /** @var InvitationDtoManagerInterface */
    private $invitationManager;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, InvitationDtoManagerInterface $invitationManager)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->invitationManager = $invitationManager;
    }


    /**
     * Answers an invitation
     *
     * @Rest\Post(path="/answer", name="rest_answer_invitation")
     *
     * @Operation(tags={ "Invitation" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The invitation identifier"),
     *   @SWG\Parameter(in="body", name="answer", required=true,
     *     @SWG\Schema(required={ "accepted" },
     *       @SWG\Property(property="accepted", type="boolean", description="The answer value"))),
     *   @SWG\Response(response=200, description="Invitation answered"),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied"),
     *   @SWG\Response(response=404, description="No invitation found")
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
    public function answerInvitationAction(int $id, Request $request)
    {
        $this->logger->debug("Answering an invitation", array ("id" => $id, "postParams" => $request->request->all()));

        /** @var InvitationDto $invitation */
        $invitation = $this->invitationManager->read($id);

        $this->evaluateUserAccess(InvitationVoter::ANSWER, $invitation);
        $this->invitationManager->answer($invitation, $request->request->getBoolean("accepted"));

        $this->logger->info("Invitation answered", array ("invitation" => $invitation));

        return new JsonResponse("Invitation answered");
    }


    /**
     * Deletes an invitation
     *
     * @Rest\Delete(name="rest_delete_invitation")
     *
     * @Operation(tags={ "Invitation" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The invitation identifier"),
     *   @SWG\Response(response=204, description="Invitation deleted"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Access denied")
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteInvitationAction(int $id)
    {
        $this->logger->debug("Deleting an invitation", array ("id" => $id));

        try
        {
            /** @var InvitationDto $invitation */
            $invitation = $this->invitationManager->read($id);

            $this->evaluateUserAccess(InvitationVoter::DELETE, $invitation);
            $this->invitationManager->delete($invitation);

            $this->logger->info("Invitation deleted", array ("id" => $id));
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->warning("Trying to delete a non existing invitation", array ("id" => $id));
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
