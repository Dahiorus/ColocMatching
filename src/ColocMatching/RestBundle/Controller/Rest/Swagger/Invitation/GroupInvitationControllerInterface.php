<?php

namespace ColocMatching\RestBundle\Controller\Rest\Swagger\Invitation;

use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Exception\InvitationNotFoundException;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @SWG\Definition(
 *   definition="GroupInvitationListResponse",
 *   allOf={
 *     {"$ref"="#/definitions/PageResponse"}
 *   },
 *   @SWG\Property(property="data", type="array",
 *     @SWG\Items(ref="#/definitions/GroupInvitation")
 * ))
 *
 * @SWG\Definition(
 *   definition="GroupInvitationResponse",
 *   allOf={
 *     {"$ref"="#/definitions/EntityResponse"}
 *   },
 *   @SWG\Property(property="content", ref="#/definitions/GroupInvitation")
 * )
 *
 * @SWG\Tag(name="Invitations - groups", description="Group invitations")
 */
interface GroupInvitationControllerInterface {

    /**
     * Lists the invitations on a group with pagination
     *
     * @SWG\Get(path="/groups/{id}/invitations", operationId="rest_get_group_invitations",
     *   tags={ "Invitations - groups" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
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
     *
     *   @SWG\Response(response=200, description="Invitations found",
     *     @SWG\Schema(ref="#/definitions/GroupInvitationListResponse")
     *   ),
     *   @SWG\Response(response=206, description="Partial content found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    function getInvitationsAction(int $id, ParamFetcher $paramFetcher);


    /**
     * Creates an invitation on a group
     *
     * @SWG\Post(path="/groups/{id}/invitations", operationId="rest_create_group_invitation",
     *   tags={ "Invitations - groups" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="invitation", required=true,
     *     description="The data to post",
     *
     *     @SWG\Schema(@SWG\Property(property="message", type="string", required=false))
     *   ),
     *
     *   @SWG\Response(response=201, description="Invitation created",
     *     @SWG\Schema(ref="#/definitions/GroupInvitationResponse")
     *   ),
     *   @SWG\Response(response=400, description="Bad request"),
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
    function createInvitationAction(int $id, Request $request);


    /**
     * Gets an invitation of a group
     *
     * @SWG\Get(path="/groups/{id}/invitations/{invitationId}", operationId="rest_get_group_invitation",
     *   tags={ "Invitations - groups" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="invitationId", type="integer", required=true,
     *     description="The invitation id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Invitation found",
     *     @SWG\Schema(ref="#/definitions/GroupInvitationResponse")
     *   ),
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
    function getInvitationAction(int $id, int $invitationId);


    /**
     * Deletes an invitation of a group
     *
     * @SWG\Delete(path="/groups/{id}/invitations/{invitationId}", operationId="rest_delete_group_invitation",
     *   tags={ "Invitations - groups" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="invitationId", type="integer", required=true,
     *     description="The invitation id"
     *   ),
     *
     *   @SWG\Response(response=200, description="Invitation deleted"),
     *   @SWG\Response(response=404, description="Group not found")
     * )
     *
     * @param int $id
     * @param int $invitationId
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     */
    function deleteInvitationAction(int $id, int $invitationId);


    /**
     * Answers an invitation of an announcement
     *
     * @SWG\Post(path="/groups/{id}/invitations/{invitationId}/answer",
     *   operationId="rest_answer_group_invitation",
     *   tags={ "Invitations - groups" },
     *
     *   @SWG\Parameter(
     *     in="path", name="id", type="integer", required=true,
     *     description="The group id"
     *   ),
     *   @SWG\Parameter(
     *     in="path", name="invitationId", type="integer", required=true,
     *     description="The invitation id"
     *   ),
     *   @SWG\Parameter(
     *     in="body", name="answer", required=true, description="The data to post",
     *
     *     @SWG\Schema(@SWG\Property(property="accepted", type="boolean", required=true))
     *   ),
     *
     *   @SWG\Response(response=200, description="Invitation answered"),
     *   @SWG\Response(response=403, description="The current user cannot answer the invitation"),
     *   @SWG\Response(response=404, description="Group or invitation not found"),
     *   @SWG\Response(response=422, description="The invitation was already answered")
     * )
     *
     * @param int $id
     * @param int $invitationId
     * @param Request $request
     *
     * @return JsonResponse
     * @throws GroupNotFoundException
     * @throws AccessDeniedException
     * @throws UnprocessableEntityHttpException
     */
    function answerInvitationAction(int $id, int $invitationId, Request $request);

}