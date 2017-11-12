<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvitationNotFoundException;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\Invitation\GroupInvitationControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * REST controller for resources /groups/{id}/invitations
 *
 * @Rest\Route("/groups/{id}/invitations", requirements={ "id": "\d+", "invitationId": "\d+" })
 *
 * @author Dahiorus
 */
class GroupInvitationController extends RestController implements GroupInvitationControllerInterface {

    /**
     * Lists the invitations of a group with pagination
     *
     * @Rest\Get(path="", name="rest_get_group_invitations")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="id")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="asc")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    function getInvitationsAction(int $id, ParamFetcher $paramFetcher) {
        $pageable = $this->extractPageableParameters($paramFetcher);

        $this->get("logger")->info("Listing invitations of a group",
            array ("group Id" => $id, "pagination" => $pageable));

        /** @var PageableFilter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($pageable["page"],
            $pageable["size"], $pageable["order"], $pageable["sort"]);
        /** @var InvitationManagerInterface */
        $manager = $this->get("coloc_matching.core.group_invitation_manager");
        /** @var Group */
        $group = $this->get("coloc_matching.core.group_manager")->read($id);
        /** @var array */
        $visits = $manager->listByInvitable($group, $filter);
        /** @var PageResponse */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($visits,
            $manager->countByInvitable($group), $filter);

        $this->get("logger")->info("Listing invitations of a group - result information",
            array ("filter" => $filter, "response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Creates an invitation on an group
     *
     * @Rest\Post(path="", name="rest_create_group_invitation")
     * @Security(expression="has_role('ROLE_SEARCH')")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    function createInvitationAction(int $id, Request $request) {
        $this->get("logger")->info("Posting a new invitation on a group",
            array ("id" => $id, "request" => $request->request));

        /** @var User $user */
        $user = $this->extractUser($request);
        /** @var Group $group */
        $group = $this->get("coloc_matching.core.group_manager")->read($id);
        /** @var Invitation $invitation */
        $invitation = $this->get("coloc_matching.core.group_invitation_manager")->create($group, $user,
            Invitation::SOURCE_SEARCH, $request->request->all());

        $this->get("logger")->info("Invitation created", array ("response" => $invitation));

        return $this->buildJsonResponse($invitation, Response::HTTP_CREATED,
            array ("Location" => sprintf("%s/%d", $request->getUri(), $invitation->getId())));
    }


    /**
     * Gets an invitation of an group
     *
     * @Rest\Get(path="/{invitationId}", name="rest_get_group_invitation")
     *
     * @param int $id
     * @param int $invitationId
     *
     * @return JsonResponse
     */
    function getInvitationAction(int $id, int $invitationId) {
        $this->get("logger")->info("Getting an invitation of an announcement",
            array ("id" => $id, "invitationId" => $invitationId));

        /** @var Invitation */
        $invitation = $this->getInvitation($id, $invitationId);

        $this->get("logger")->info("One invitation found", array ("response" => $invitation));

        return $this->buildJsonResponse($invitation, Response::HTTP_OK);
    }


    /**
     * Deletes an invitation of an group
     *
     * @Rest\Delete(path="/{invitationId}", name="rest_delete_group_invitation")
     *
     * @param int $id
     * @param int $invitationId
     *
     * @return JsonResponse
     */
    function deleteInvitationAction(int $id, int $invitationId) {
        $this->get("logger")->info("Deleting an invitation", array ("id" => $id, "invitationId" => $invitationId));

        $this->get("coloc_matching.core.group_manager")->read($id);

        try {
            /** @var InvitationManagerInterface $manager */
            $manager = $this->get("coloc_matching.core.group_invitation_manager");
            /** @var Invitation $invitation */
            $invitation = $manager->read($invitationId);

            if (!empty($invitation)) {
                $this->get("logger")->debug("Invitation found", array ("invitation" => $invitation));

                $manager->delete($invitation);
            }
        }
        catch (InvitationNotFoundException $e) {
            $this->get("logger")->warn("No invitation found", array ("id" => $id, "invitationId" => $invitationId));
        }

        return new JsonResponse("Invitation deleted");
    }


    /**
     * Answers an invitation of an group
     *
     * @Rest\Post(path="/{invitationId}/answer", name="rest_answer_group_invitation")
     *
     * @param int $id
     * @param int $invitationId
     * @param Request $request
     *
     * @return JsonResponse
     */
    function answerInvitationAction(int $id, int $invitationId, Request $request) {
        $this->get("logger")->info("Answering an invitation",
            array ("id" => $id, "invitationId" => $id, "request" => $request));

        /** @var User $user */
        $user = $this->extractUser($request);
        /** @var Invitation $invitation */
        $invitation = $this->getInvitation($id, $invitationId);

        if (!$this->isAnswerPossible($invitation, $user)) {
            throw new AccessDeniedException("The current user cannot answer the invitation");
        }

        $this->get("coloc_matching.core.group_invitation_manager")->answer($invitation,
            $request->request->getBoolean("accepted"));

        $this->get("logger")->info("Invitation answered");

        return new JsonResponse("Invitation answered");
    }


    private function getInvitation(int $groupId, int $id) : Invitation {
        /** @var Group $group */
        $group = $this->get("coloc_matching.core.group_manager")->read($groupId);
        /** @var Invitation $invitation */
        $invitation = $this->get("coloc_matching.core.group_invitation_manager")->read($id);

        if ($invitation->getInvitable() !== $group) {
            throw new InvitationNotFoundException("id", $id);
        }

        return $invitation;
    }


    private function isAnswerPossible(Invitation $invitation, User $user) : bool {
        if ($invitation->getSourceType() == Invitation::SOURCE_SEARCH) {
            // only the creator of the group can answer the invitation
            return $invitation->getInvitable() === $user->getGroup();
        }
        else if ($invitation->getSourceType() == Invitation::SOURCE_INVITABLE) {
            // only the invitee can answer the invitation
            return $user === $invitation->getRecipient();
        }

        return false;
    }

}