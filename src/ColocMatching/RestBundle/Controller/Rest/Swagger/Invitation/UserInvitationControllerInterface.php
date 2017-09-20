<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Invitation;

use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Exception\InvitationNotFoundException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @SWG\Tag(name="Invitations - users", description="User's invitations")
 */
interface UserInvitationControllerInterface {

    /**
     * Lists the invitations with the user as the recipient with pagination
     *
     * @SWG\Get(path="/users/{id}/invitations", operationId="rest_get_user_invitations",
     *   tags={ "Invitations - users" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The user id"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="page", type="integer", default=1, minimum=0,
     *     description="The page of the paginated search"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="size", type="integer", default=20, minimum=1,
     *     description="The number of results to return"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="sort", type="string", default="id",
     *     description="The name of the attribute to order the results"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="order", type="string", enum={"asc", "desc"}, default="asc",
     *     description="The sort direction ('asc' for ascending sort, 'desc' for descending sort)"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="type", type="string", enum={"announcement", "group"}, required=true,
     *     description="The invitable type"
     *   ),
     *
     *   @SWG\Response(response=200, description="Invitations found",
     *     @SWG\Schema(ref="#/definitions/AnnouncementInvitationListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=404, description="User not found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getInvitationsAction(int $id, ParamFetcher $paramFetcher);


    /**
     * Creates an invitation with the user as the recipient
     *
     * @SWG\Post(path="/users/{id}/invitations", operationId="rest_create_user_invitation",
     *   tags={ "Invitations - users" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The user id"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="type", type="string", enum={"announcement", "group"}, required=true,
     *     description="The invitable type"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="invitation", required=true, description="The data to post",
     *
     *     @SWG\Schema(@SWG\Property(property="message", type="string"))
     *   ),
     *
     *   @SWG\Response(response=201, description="Invitation created",
     *     @SWG\Schema(ref="#/definitions/GroupInvitationResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=403, description="Only users owning an invitable can create an invitation"),
     *   @SWG\Response(response=404, description="User not found"),
     *   @SWG\Response(response=422, description="Cannot create an invitation")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     * @throws UnprocessableEntityHttpException
     */
    public function createInvitationAction(int $id, Request $request);


    /**
     * Gets an invitation of a user
     *
     * @SWG\Get(path="/users/{id}/invitations/{invitationId}", operationId="rest_get_user_invitation",
     *   tags={ "Invitations - users" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The user id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="invitationId", type="integer", required=true,
     *     description="The invitation id"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="type", type="string", enum={"announcement", "group"}, required=true,
     *     description="The invitable type"
     *   ),
     *
     *   @SWG\Response(response=200, description="Invitation found",
     *     @SWG\Schema(ref="#/definitions/AnnouncementInvitationResponse")
     *   ),
     *   @SWG\Response(response=404, description="User or invitation not found")
     * )
     *
     * @param int $id
     * @param int $invitationId
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     * @throws InvitationNotFoundException
     */
    public function getInvitationAction(int $id, int $invitationId, ParamFetcher $paramFetcher);


    /**
     * Deletes an invitation of a user
     *
     * @SWG\Delete(path="/users/{id}/invitations/{invitationId}",
     *   operationId="rest_delete_announcement_invitation",
     *   tags={ "Invitations - users" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The user id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="invitationId", type="integer", required=true,
     *     description="The invitation id"
     *   ),
     *   @SWG\Parameter(
     *     in="query", name="type", type="string", enum={"announcement", "group"}, required=true,
     *     description="The invitable type"
     *   ),
     *
     *   @SWG\Response(response=200, description="Invitation deleted"),
     *   @SWG\Response(response=404, description="User not found")
     * )
     *
     * @param int $id
     * @param int $invitationId
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     * @throws GroupNotFoundException
     */
    public function deleteInvitationAction(int $id, int $invitationId, ParamFetcher $paramFetcher);

}