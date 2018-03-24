<?php

namespace ColocMatching\CoreBundle\Manager\Invitation;

use ColocMatching\CoreBundle\DTO\Invitation\InvitationDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Exception\InvalidRecipientException;
use ColocMatching\CoreBundle\Exception\UnavailableInvitableException;
use ColocMatching\CoreBundle\Form\Type\Invitation\InvitationDtoForm;
use ColocMatching\CoreBundle\Manager\AbstractDtoManager;
use ColocMatching\CoreBundle\Mapper\Invitation\InvitationDtoMapper;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Invitation\InvitationRepository;
use ColocMatching\CoreBundle\Validator\FormValidator;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

abstract class InvitationDtoManager extends AbstractDtoManager implements InvitationDtoManagerInterface
{
    /** @var InvitationRepository */
    protected $repository;

    /** @var InvitationDtoMapper */
    protected $dtoMapper;

    /** @var FormValidator */
    private $formValidator;

    /** @var UserDtoMapper */
    private $userDtoMapper;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, InvitationDtoMapper $dtoMapper,
        FormValidator $formValidator, UserDtoMapper $userDtoMapper)
    {
        parent::__construct($logger, $em, $dtoMapper);
        $this->formValidator = $formValidator;
        $this->userDtoMapper = $userDtoMapper;
    }


    /**
     * @inheritdoc
     */
    public function create(int $invitableId, UserDto $recipient, string $sourceType, array $data,
        bool $flush = true) : InvitationDto
    {
        $this->logger->debug("Creating an invitation for an entity",
            array ("invitable" => array ($this->getInvitableClass() => $invitableId),
                "recipient" => $recipient, "sourceType" => $sourceType, "data" => $data, "flush" => $flush));

        $invitable = $this->getInvitable($invitableId);
        $userEntity = $this->userDtoMapper->toEntity($recipient);

        if (!$invitable->isAvailable())
        {
            throw new UnavailableInvitableException($invitable, "The invitable is unavailable");
        }

        if (!$userEntity->isEnabled())
        {
            throw new InvalidRecipientException($userEntity, "The recipient is not enabled");
        }

        $data["sourceType"] = $sourceType;

        /** @var InvitationDto $invitation */
        $invitation = $this->formValidator->validateDtoForm(InvitationDto::create($this->getInvitableClass()), $data,
            InvitationDtoForm::class, true);
        $invitation->setRecipientId($recipient->getId());
        $invitation->setInvitableId($invitableId);

        /** @var Invitation $entity */
        $entity = $this->dtoMapper->toEntity($invitation);

        $this->em->persist($entity);
        $this->flush($flush);

        return $this->dtoMapper->toDto($entity);
    }


    /**
     * @inheritdoc
     */
    public function answer(InvitationDto $invitation, bool $accepted, bool $flush = true) : InvitationDto
    {
        $this->logger->debug("Answering an invitation", array ("invitation" => $invitation, "accepted" => $accepted));

        if ($invitation->getStatus() !== Invitation::STATUS_WAITING)
        {
            throw new InvalidParameterException("invitation", "The invitation was already answered");
        }

        /** @var Invitation $entity */
        $entity = $this->dtoMapper->toEntity($invitation);

        if ($accepted)
        {
            $entity->setStatus(Invitation::STATUS_ACCEPTED);

            $recipient = $entity->getRecipient();
            $invitable = $entity->getInvitable();
            $invitable->addInvitee($recipient);
            $this->purge($entity);

            if ($invitation->getSourceType() == Invitation::SOURCE_INVITABLE && $recipient->hasGroup())
            {
                $this->inviteMembers($recipient, $invitable);
            }

            $this->em->merge($invitable);
        }
        else
        {
            $entity->setStatus(Invitation::STATUS_REFUSED);
        }

        $this->em->merge($entity);
        $this->flush($flush);

        return $this->dtoMapper->toDto($entity);
    }


    /**
     * @inheritdoc
     */
    public function listByRecipient(UserDto $recipient, PageableFilter $filter) : array
    {
        $this->logger->debug("Getting invitations of a recipient",
            array ("recipient" => $recipient, "filter" => $filter));

        /** @var User $userEntity */
        $userEntity = $this->userDtoMapper->toEntity($recipient);

        return $this->convertEntityListToDto($this->repository->findByRecipient($userEntity, $filter));
    }


    /**
     * @inheritdoc
     */
    public function countByRecipient(UserDto $recipient) : int
    {
        $this->logger->debug("Counting invitations of a recipient", array ("recipient" => $recipient));

        /** @var User $userEntity */
        $userEntity = $this->userDtoMapper->toEntity($recipient);

        return $this->repository->countByRecipient($userEntity);
    }


    /**
     * @inheritdoc
     */
    public function listByInvitable(int $invitableId, PageableFilter $filter) : array
    {
        $this->logger->debug("Getting invitations of an invitable",
            array ("invitable" => array ($this->getInvitableClass() => $invitableId), "filter" => $filter));

        /** @var Invitable $invitable */
        $invitable = $this->getInvitable($invitableId);

        return $this->convertEntityListToDto($this->repository->findByInvitable($invitable, $filter));
    }


    /**
     * @inheritdoc
     */
    public function countByInvitable(int $invitableId) : int
    {
        $this->logger->debug("Counting invitations of a recipient",
            array ("invitable" => array ($this->getInvitableClass() => $invitableId)));

        /** @var Invitable $invitable */
        $invitable = $this->getInvitable($invitableId);

        return $this->repository->countByInvitable($invitable);
    }


    /**
     * Gets an Invitable by its identifier
     *
     * @param int $id The Invitable identifier
     *
     * @return Invitable
     * @throws EntityNotFoundException
     */
    private function getInvitable(int $id) : Invitable
    {
        /** @var Invitable $invitable */
        $invitable = $this->em->find($this->getInvitableClass(), $id);

        if (empty($invitable))
        {
            throw new EntityNotFoundException($this->getInvitableClass(), "id", $id);
        }

        return $invitable;
    }


    /**
     * Refused all invitations in a "waiting" state which are different of the accepted invitation
     *
     * @param Invitation $invitation The accepted invitation
     *
     * @throws ORMException
     */
    private function purge(Invitation $invitation)
    {
        $filter = new InvitationFilter();
        $filter->setRecipientId($invitation->getRecipient()->getId());
        $filter->setStatus(Invitation::STATUS_WAITING);

        /** @var Invitation[] $others */
        $others = array_filter($this->repository->findAllBy($filter), function (Invitation $other) use ($invitation) {
            return $other->getId() != $invitation->getId();
        });
        array_walk($others, function (Invitation $i) {
            $i->setStatus(Invitation::STATUS_REFUSED);
            $this->em->merge($i);
        });
    }


    /**
     * Invites all other members of the invitee group
     *
     * @param User $invitee The invitee who will join the invitable
     * @param Invitable $invitable The group or the announcement to join
     */
    private function inviteMembers(User $invitee, Invitable $invitable)
    {
        $this->logger->debug("Sending an invitation to all others members of the invitee group",
            array ("invitee" => $invitee));

        /** @var Collection $members */
        $members = $invitee->getGroup()->getMembers()->filter(function (User $member) use ($invitee) {
            return $member->getId() != $invitee->getId();
        });
        $members->forAll(function (User $member) use ($invitable) {
            $this->em->persist(Invitation::create($invitable, $member, Invitation::SOURCE_INVITABLE));
        });
    }


    /**
     * Gets the invitable entity class
     * @return string
     */
    protected abstract function getInvitableClass() : string;

}