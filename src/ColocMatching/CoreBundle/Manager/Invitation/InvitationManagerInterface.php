<?php

namespace ColocMatching\CoreBundle\Manager\Invitation;

use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Manager\ManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

interface InvitationManagerInterface extends ManagerInterface {

    /**
     * Creates an invitation
     *
     * @param Invitable $invitable The invitable of the invitation
     * @param User $recipient      The recipient of the invitation
     * @param string $sourceType   The source type of the invitation
     * @param array $data          The data of the invitation
     *
     * @return Invitation
     * @throws  InvalidFormDataException
     * @throws UnprocessableEntityHttpException
     */
    function create(Invitable $invitable, User $recipient, string $sourceType, array $data) : Invitation;


    /**
     * Accepts or refused an invitation. If true, then adds the recipient in the invitable invitees.
     *
     * @param Invitation $invitation The invitation to answer
     * @param bool $accepted         If true then adds the recipient of the invitation in the invitable invitees
     *
     * @return Invitation
     */
    function answer(Invitation $invitation, bool $accepted) : Invitation;


    /**
     * Deletes an invitation
     *
     * @param Invitation $invitation The invitation to delete
     */
    function delete(Invitation $invitation);


    /**
     * Lists the invitations of a recipient with pagination
     *
     * @param User $recipient        The recipient of the invitations
     * @param PageableFilter $filter The pagination filter
     *
     * @return array<Invitation>
     */
    function listByRecipient(User $recipient, PageableFilter $filter) : array;


    /**
     * Counts the invitations of a recipient
     *
     * @param User $recipient The recipient of the invitations
     *
     * @return int
     */
    function countByRecipient(User $recipient) : int;


    /**
     * Lists the invitations of an invitable with pagination
     *
     * @param Invitable $invitable   The inivitable of the invitations
     * @param PageableFilter $filter The pagination filter
     *
     * @return array<Invitation>
     */
    function listByInvitable(Invitable $invitable, PageableFilter $filter) : array;


    /**
     * Counts the invitations of an inivitable
     *
     * @param Invitable $invitable The inivitable of the invitations
     *
     * @return int
     */
    function countByInvitable(Invitable $invitable) : int;


    /**
     * Searches invitations corresponding the filter
     *
     * @param InvitationFilter $filter The search filter criteria
     * @param array|null $fields       The fields to return
     *
     * @return array<Invitation>
     */
    function search(InvitationFilter $filter, array $fields = null) : array;


    /**
     * Counts the invitations corresponding to the filter
     *
     * @param InvitationFilter $filter The search filter criteria
     *
     * @return int
     */
    function countBy(InvitationFilter $filter) : int;
}
