<?php

namespace App\Core\Manager\Invitation;

use App\Core\DTO\Invitation\InvitableDto;
use App\Core\DTO\Invitation\InvitationDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Invitation\Invitable;
use App\Core\Entity\Invitation\Invitation;
use App\Core\Entity\User\User;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Exception\InvalidRecipientException;
use App\Core\Exception\UnavailableInvitableException;
use App\Core\Form\Type\Invitation\InvitationDtoForm;
use App\Core\Manager\AbstractDtoManager;
use App\Core\Mapper\Invitation\InvitationDtoMapper;
use App\Core\Mapper\User\UserDtoMapper;
use App\Core\Repository\Filter\InvitationFilter;
use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Repository\Invitation\InvitationRepository;
use App\Core\Validator\FormValidator;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;

class InvitationDtoManager extends AbstractDtoManager implements InvitationDtoManagerInterface
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


    public function create(InvitableDto $invitable, UserDto $recipient, string $sourceType, array $data,
        bool $flush = true) : InvitationDto
    {
        $this->logger->debug("Creating an invitation for an entity",
            array ("invitable" => $invitable, "recipient" => $recipient, "sourceType" => $sourceType, "data" => $data,
                "flush" => $flush));

        $userEntity = $this->userDtoMapper->toEntity($recipient);
        /** @var Invitable $invitableEntity */
        $invitableEntity = $this->getInvitable($invitable->getEntityClass(), $invitable->getId());

        if (!$invitableEntity->isAvailable())
        {
            throw new UnavailableInvitableException($invitableEntity, "The invitable is unavailable");
        }

        if (!$userEntity->isEnabled())
        {
            throw new InvalidRecipientException($userEntity, "The recipient is not enabled");
        }

        /** @var InvitationDto $invitation */
        $invitation = $this->formValidator->validateDtoForm(InvitationDto::create($invitable, $recipient, $sourceType),
            $data, InvitationDtoForm::class, true);

        /** @var Invitation $entity */
        $entity = $this->dtoMapper->toEntity($invitation);

        $this->em->persist($entity);
        $this->flush($flush);

        $this->logger->info("Invitation created", array ("invitation" => $entity));

        return $this->dtoMapper->toDto($entity);
    }


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
            $invitable = $this->getInvitable($invitation->getInvitableClass(), $invitation->getInvitableId());
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

        $entity = $this->em->merge($entity);
        $this->flush($flush);

        $this->logger->info("Invitation answered", array ("invitation" => $entity));

        return $this->dtoMapper->toDto($entity);
    }


    public function listByRecipient(UserDto $recipient, Pageable $pageable = null)
    {
        $this->logger->debug("Getting invitations of a recipient",
            array ("recipient" => $recipient, "pageable" => $pageable));

        $user = $this->userDtoMapper->toEntity($recipient);
        $entities = $this->repository->findByRecipient($user, $pageable);

        $this->logger->info("Invitations found", array ("entities" => $entities));

        return $this->buildDtoCollection($entities, $this->repository->countByRecipient($user), $pageable);
    }


    public function countByRecipient(UserDto $recipient) : int
    {
        $this->logger->debug("Counting invitations of a recipient", array ("recipient" => $recipient));

        $filter = new InvitationFilter();
        $filter->setRecipientId($recipient->getId());

        return $this->repository->countByFilter($filter);
    }


    public function listByInvitable(InvitableDto $invitable, Pageable $pageable = null)
    {
        $this->logger->debug("Getting invitations of an invitable",
            array ("invitable" => $invitable, "pageable" => $pageable));

        $filter = new InvitationFilter();
        $filter->setInvitableClass($invitable->getEntityClass());
        $filter->setInvitableId($invitable->getId());

        $entities = $this->repository->findByFilter($filter, $pageable);

        $this->logger->info("Invitations found", array ("entities" => $entities));

        return $this->buildDtoCollection($entities, $this->countByInvitable($invitable), $pageable);
    }


    public function countByInvitable(InvitableDto $invitable) : int
    {
        $this->logger->debug("Counting invitations of a recipient", array ("invitable" => $invitable));

        $filter = new InvitationFilter();
        $filter->setInvitableClass($invitable->getEntityClass());
        $filter->setInvitableId($invitable->getId());

        return $this->repository->countByFilter($filter);
    }


    /**
     * Gets an Invitable by its identifier
     *
     * @param string $invitableClass The invitable entity class
     * @param int $id The invitable identifier
     *
     * @return Invitable
     * @throws EntityNotFoundException
     */
    private function getInvitable(string $invitableClass, int $id) : Invitable
    {
        $repository = $this->em->getRepository($invitableClass);
        /** @var Invitable $invitable */
        $invitable = $repository->find($id);

        if (empty($invitable))
        {
            throw new EntityNotFoundException($invitableClass, "id", $id);
        }

        $this->logger->debug("Invitation invitable found", array ("invitable" => $invitable));

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
        $others = array_filter($this->repository->findByFilter($filter),
            function (Invitation $other) use ($invitation) {
                return $other->getId() != $invitation->getId();
            });
        array_walk($others, function (Invitation $i) {
            $i->setStatus(Invitation::STATUS_REFUSED);
            $i = $this->em->merge($i);

            $this->logger->debug("Invitation refused", array ("invitation" => $i));
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
            $entity = new Invitation(get_class($invitable), $invitable->getId(), $member, Invitation::SOURCE_INVITABLE);
            $this->em->persist($entity);

            $this->logger->debug("Invitation created for the group member", array ("invitation" => $entity));
        });
    }


    protected function getDomainClass() : string
    {
        return Invitation::class;
    }

}