<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Invitation;

use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\InvitationNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Filter\InvitationFilterType;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use ColocMatching\RestBundle\Controller\Response\EntityResponse;
use ColocMatching\RestBundle\Controller\Response\PageResponse;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\v1\Swagger\Invitation\UserInvitationControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * REST controller for resources /users/{id}/invitations
 *
 * @Rest\Route("/users/{id}/invitations", requirements={ "id": "\d+", "invitationId": "\d+" })
 *
 * @author Dahiorus
 */
class UserInvitationController extends RestController implements UserInvitationControllerInterface {

    private const ANNOUNCEMENT = "announcement";
    private const GROUP = "group";


    /**
     * Lists the invitations with the user as the recipient with pagination
     *
     * @Rest\Get(path="", name="rest_get_user_invitations")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     * @Rest\QueryParam(name="sort", nullable=true, description="The name of the attribute to order the results",
     *   default="id")
     * @Rest\QueryParam(name="order", nullable=true, description="The sorting direction", requirements="^(asc|desc)$",
     *   default="asc")
     * @Rest\QueryParam(name="type", nullable=false, description="The invitable type",
     *   requirements="^(announcement|group)$")
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getInvitationsAction(int $id, ParamFetcher $paramFetcher) {
        $pageable = $this->extractPageableParameters($paramFetcher);
        $invitableType = $paramFetcher->get("type");

        $this->get("logger")->info("Getting invitations of a user",
            array ("id" => $id, "pageable" => $pageable, "invitableType" => $invitableType));

        $this->get("coloc_matching.core.user_manager")->read($id);

        $pageable["recipientId"] = $id;
        /** @var InvitationFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")->buildCriteriaFilter(InvitationFilterType::class,
            new InvitationFilter(), $pageable);
        /** @var array<Invitation> $invitations */
        $invitations = $this->getManager($invitableType)->search($filter);
        /** @var PageResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($invitations,
            $this->getManager($invitableType)->countBy($filter), $filter);

        $this->get("logger")->info("Getting invitations of a user - result information",
            array ("response" => $response));

        return $this->buildJsonResponse($response,
            $response->hasNext() ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Creates an invitation with the user as the recipient
     *
     * @Rest\Post(path="", name="rest_create_user_invitation")
     * @Rest\QueryParam(name="type", nullable=false, description="The invitable type",
     *   requirements="^(announcement|group)$")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createInvitationAction(int $id, Request $request) {
        $invitableType = $request->query->get("type");

        $this->get("logger")->info("Creating an invitation for a user",
            array ("id" => $id, "invitableType" => $invitableType, "request" => $request));

        /** @var User $user */
        $user = $this->extractUser($request);
        /** @var User $recipient */
        $recipient = $this->get("coloc_matching.core.user_manager")->read($id);

        if ($user === $recipient || !self::isCreationPossible($user)) {
            throw new AccessDeniedException("This user cannot create an invitation");
        }

        try {
            /** @var Invitation $invitation */
            $invitation = $this->getManager($invitableType)->create(self::getInvitable($invitableType, $user),
                $recipient, Invitation::SOURCE_INVITABLE, $request->request->all());
            $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($invitation,
                sprintf("%s/%d", $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo(),
                    $invitation->getId()));

            $this->get("logger")->info("Invitation created", array ("response" => $response));

            return $this->buildJsonResponse($response, Response::HTTP_CREATED,
                array ("location" => $response->getLink()));
        }
        catch (InvalidFormDataException $e) {
            $this->get("logger")->error("Error while creating an invitation",
                array ("request" => $request, "exception" => $e));

            return $this->buildBadRequestResponse($e);
        }
    }


    /**
     * Gets an invitation of a user
     *
     * @Rest\Get(path="/{invitationId}", name="rest_get_user_invitation")
     * @Rest\QueryParam(name="type", nullable=false, description="The invitable type",
     *   requirements="^(announcement|group)$")
     *
     * @param int $id
     * @param int $invitationId
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function getInvitationAction(int $id, int $invitationId, ParamFetcher $paramFetcher) {
        $invitableType = $paramFetcher->get("type");

        $this->get("logger")->info("Getting an invitation of a user",
            array ("id" => $id, "invitationId" => $invitationId, "invitableType" => $invitableType));

        /** @var Invitation $invitation */
        $invitation = $this->getInvitation($id, $invitationId, $invitableType);
        /** @var EntityResponse $response */
        $response = $this->get("coloc_matching.rest.response_factory")->createEntityResponse($invitation);

        $this->get("logger")->info("One invitation found", array ("response" => $response));

        return $this->buildJsonResponse($response, Response::HTTP_OK);
    }


    /**
     * Deletes an invitation of a user
     *
     * @Rest\Delete(path="/{invitationId}", name="rest_delete_user_invitation")
     * @Rest\QueryParam(name="type", nullable=false, description="The invitable type",
     *   requirements="^(announcement|group)$")
     *
     * @param int $id
     * @param int $invitationId
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     */
    public function deleteInvitationAction(int $id, int $invitationId, ParamFetcher $paramFetcher) {
        $invitableType = $paramFetcher->get("type");

        $this->get("logger")->info("Deleting an invitation of a user",
            array ("id" => $id, "invitationId" => $invitationId, "invitableType" => $invitableType));

        $this->get("coloc_matching.core.user_manager")->read($id);

        try {
            /** @var InvitationManagerInterface $manager */
            $manager = $this->getManager($invitableType);
            /** @var Invitation $invitation */
            $invitation = $manager->read($invitationId);

            if (!empty($invitation)) {
                $this->get("logger")->debug("Invitation found", array ("invitation" => $invitation));

                $manager->delete($invitation);
            }
        }
        catch (InvitationNotFoundException $e) {
            // nothing to do
        }

        return new JsonResponse("Invitation deleted");
    }


    /**
     * @param string $invitableType
     *
     * @return InvitationManagerInterface
     * @throws \Exception
     */
    private function getManager(string $invitableType) : InvitationManagerInterface {
        $manager = null;

        switch ($invitableType) {
            case self::ANNOUNCEMENT:
                $manager = $this->get("coloc_matching.core.announcement_invitation_manager");
                break;
            case self::GROUP:
                $manager = $this->get("coloc_matching.core.group_invitation_manager");
                break;
            default:
                throw new \Exception("Unknown invitable type");
                break;
        }

        return $manager;
    }


    private function getInvitation(int $userId, int $invitationId, string $invitableType) : Invitation {
        /** @var User $user */
        $user = $this->get("coloc_matching.core.user_manager")->read($userId);
        /** @var Invitation $invitation */
        $invitation = $this->getManager($invitableType)->read($invitationId);

        if ($user !== $invitation->getRecipient()) {
            throw new InvitationNotFoundException("id", $userId);
        }

        return $invitation;
    }


    /**
     * @param string $invitableType
     * @param User $user
     *
     * @return Invitable|null
     * @throws \Exception
     */
    private static function getInvitable(string $invitableType, User $user) {
        $invitable = null;

        switch ($invitableType) {
            case self::ANNOUNCEMENT:
                $invitable = $user->getAnnouncement();
                break;
            case self::GROUP:
                $invitable = $user->getGroup();
                break;
            default:
                throw new \Exception("Unknown invitable type");
                break;
        }

        return $invitable;
    }


    private static function isCreationPossible(User $user) : bool {
        return ($user->getType() == UserConstants::TYPE_SEARCH && $user->getGroup() !== null)
            || ($user->getType() == UserConstants::TYPE_PROPOSAL && $user->getAnnouncement() !== null);
    }
}