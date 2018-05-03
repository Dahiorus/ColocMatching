<?php

namespace ColocMatching\CoreBundle\Manager\Invitation;

use ColocMatching\CoreBundle\DTO\Invitation\InvitableDto;
use ColocMatching\CoreBundle\DTO\Invitation\InvitationDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Manager\DtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\ORMException;

interface InvitationDtoManagerInterface extends DtoManagerInterface
{
    /**
     * Creates an invitation
     *
     * @param InvitableDto $invitable The invitable of the invitation
     * @param UserDto $recipient The recipient of the invitation
     * @param string $sourceType The source type of the invitation
     * @param array $data The data of the invitation
     * @param bool $flush If the operation must be flushed
     *
     * @return InvitationDto
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws InvalidParameterException
     * @throws ORMException
     */
    public function create(InvitableDto $invitable, UserDto $recipient, string $sourceType, array $data,
        bool $flush = true) : InvitationDto;


    /**
     * Accepts or refused an invitation. If true, then adds the recipient in the invitable invitees.
     *
     * @param InvitationDto $invitation The invitation to answer
     * @param bool $accepted If true then adds the recipient of the invitation in the invitable invitees
     * @param bool $flush If the operation must be flushed
     *
     * @return InvitationDto
     * @throws EntityNotFoundException
     * @throws InvalidParameterException
     * @throws ORMException
     */
    public function answer(InvitationDto $invitation, bool $accepted, bool $flush = true) : InvitationDto;


    /**
     * Lists the invitations of a recipient with pagination
     *
     * @param UserDto $recipient The recipient of the invitations
     * @param Pageable $pageable [optional] The pagination filter
     *
     * @return InvitationDto[]
     * @throws ORMException
     */
    public function listByRecipient(UserDto $recipient, Pageable $pageable = null) : array;


    /**
     * Counts the invitations of a recipient
     *
     * @param UserDto $recipient The recipient of the invitations
     *
     * @return int
     * @throws ORMException
     */
    public function countByRecipient(UserDto $recipient) : int;


    /**
     * Lists an invitable invitations
     *
     * @param InvitableDto $invitable The invitations invitable
     * @param Pageable $pageable [optional] The pagination filter
     *
     * @return InvitationDto[]
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function listByInvitable(InvitableDto $invitable, Pageable $pageable = null) : array;


    /**
     * Counts an invitable invitations
     *
     * @param InvitableDto $invitable The invitations invitable
     *
     * @return int
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function countByInvitable(InvitableDto $invitable) : int;
}