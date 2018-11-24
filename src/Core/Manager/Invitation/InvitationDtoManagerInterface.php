<?php

namespace App\Core\Manager\Invitation;

use App\Core\DTO\Collection;
use App\Core\DTO\Invitation\InvitableDto;
use App\Core\DTO\Invitation\InvitationDto;
use App\Core\DTO\Page;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Manager\DtoManagerInterface;
use App\Core\Repository\Filter\Pageable\Pageable;
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
     * @return Collection|Page
     * @throws ORMException
     */
    public function listByRecipient(UserDto $recipient, Pageable $pageable = null);


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
     * @return Collection|Page
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function listByInvitable(InvitableDto $invitable, Pageable $pageable = null);


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