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
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @Rest\RequestParam(name="accepted", nullable=false, requirements="(true|false)", allowBlank=false, strict=true,
     *   description="The answer value")
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
        $this->logger->info("Answering an invitation", array ("id" => $id, "postParams" => $request->request->all()));

        /** @var InvitationDto $invitation */
        $invitation = $this->invitationManager->read($id);

        $this->evaluateUserAccess(InvitationVoter::ANSWER, $invitation);
        $this->invitationManager->answer($invitation, $request->request->getBoolean("accepted"));

        $this->logger->debug("Invitation answered", array ("invitation" => $invitation));

        return new JsonResponse("Invitation answered");
    }


    /**
     * Deletes an invitation
     *
     * @Rest\Delete(name="rest_delete_invitation")
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteInvitationAction(int $id)
    {
        $this->logger->info("Deleting an invitation", array ("id" => $id));

        try
        {
            /** @var InvitationDto $invitation */
            $invitation = $this->invitationManager->read($id);

            $this->evaluateUserAccess(InvitationVoter::DELETE, $invitation);
            $this->invitationManager->delete($invitation);

            $this->logger->debug("Invitation deleted", array ("id" => $id));
        }
        catch (EntityNotFoundException $e)
        {
            $this->logger->warning("Trying to delete a non existing invitation", array ("id" => $id));
        }

        return new JsonResponse("Invitation deleted");
    }

}
