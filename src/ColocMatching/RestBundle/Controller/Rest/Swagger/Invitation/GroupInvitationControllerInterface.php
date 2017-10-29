<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Invitation;

use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Exception\InvitationNotFoundException;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @SWG\Definition(
 *   definition="GroupInvitationPageResponse", allOf={ @SWG\Schema(ref="#/definitions/PageResponse")},
 *   @SWG\Property(property="content", type="array", @SWG\Items(ref="#/definitions/GroupInvitation"))
 * )
 * @SWG\Tag(name="Invitations - groups", description="Group invitations")
 */
interface GroupInvitationControllerInterface {

    /**
     * Lists the invitations on a group with pagination
     *
     * @SWG\Get(path="/groups/{id}/invitations", operationId="rest_get_group_invitations",
     *   tags={ "Invitations - groups" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Parameter(
     *     in="query", name="page", type="integer", default=1, minimum=0,
     *     description="The page of the paginated search"),
     *   @SWG\Parameter(
     *     in="query", name="size", type="integer", default=20, minimum=1,
     *     description="The number of results to return"),
     *   @SWG\Parameter(
     *     in="query", name="sort", type="string", default="id",
     *     description="The name of the attribute to order the results"),
     *   @SWG\Parameter(
     *     in="query", name="order", type="string", enum={"asc", "desc"}, default="asc",
     *     description="The sort direction ('asc' for ascending sort, 'desc' for descending sort)"),
     *   @SWG\Response(response=200, description="Invitations found",
     *     @SWG\Schema(ref="#/definitions/GroupInvitationPageResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function getInvitationsAction(int $id, ParamFetcher $paramFetcher);


    /**
     * Creates an invitation on a group
     *
     * @SWG\Post(path="/groups/{id}/invitations", operationId="rest_create_group_invitation",
     *   tags={ "Invitations - groups" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Parameter(
     *     in="body", name="invitation", required=true, description="The data to post",
     *     @SWG\Schema(@SWG\Property(property="message", type="string"))),
     *   @SWG\Response(
     *     response=201, description="Invitation created", @SWG\Schema(ref="#/definitions/GroupInvitation")),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Only search users can create an invitation"),
     *   @SWG\Response(response=404, description="Group not found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function createInvitationAction(int $id, Request $request);


    /**
     * Gets an invitation of a group
     *
     * @SWG\Get(path="/groups/{id}/invitations/{invitationId}", operationId="rest_get_group_invitation",
     *   tags={ "Invitations - groups" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Parameter(
     *     in="path", name="invitationId", type="integer", required=true, description="The invitation identifier"),
     *   @SWG\Response(response=200, description="Invitation found", @SWG\Schema(ref="#/definitions/GroupInvitation")),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="Group or invitation not found")
     * )
     *
     * @param int $id
     * @param int $invitationId
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     * @throws InvitationNotFoundException
     */
    public function getInvitationAction(int $id, int $invitationId);


    /**
     * Deletes an invitation of a group
     *
     * @SWG\Delete(path="/groups/{id}/invitations/{invitationId}", operationId="rest_delete_group_invitation",
     *   tags={ "Invitations - groups" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group id"),
     *   @SWG\Parameter(
     *     in="path", name="invitationId", type="integer", required=true, description="The invitation identifier"),
     *   @SWG\Response(response=200, description="Invitation deleted"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="Forbidden access"),
     *   @SWG\Response(response=404, description="Group not found")
     * )
     *
     * @param int $id
     * @param int $invitationId
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    public function deleteInvitationAction(int $id, int $invitationId);


    /**
     * Answers an invitation of an announcement
     *
     * @SWG\Post(path="/groups/{id}/invitations/{invitationId}/answer", operationId="rest_answer_group_invitation",
     *   tags={ "Invitations - groups" }, security={
     *     { "api_token" = {} }
     *   },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Parameter(
     *     in="path", name="invitationId", type="integer", required=true, description="The invitation identifier"),
     *   @SWG\Parameter(
     *     in="body", name="answer", required=true, description="The data to post",
     *     @SWG\Schema(@SWG\Property(property="accepted", type="boolean"))),
     *   @SWG\Response(response=200, description="Invitation answered"),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized access"),
     *   @SWG\Response(response=403, description="The current user cannot answer the invitation"),
     *   @SWG\Response(response=404, description="Group or invitation not found")
     * )
     *
     * @param int $id
     * @param int $invitationId
     * @param Request $request
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     * @throws InvalidParameterException
     * @throws AccessDeniedException
     */
    public function answerInvitationAction(int $id, int $invitationId, Request $request);

}