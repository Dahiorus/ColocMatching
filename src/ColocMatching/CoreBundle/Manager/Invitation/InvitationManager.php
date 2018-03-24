<?php

namespace ColocMatching\CoreBundle\Manager\Invitation;

use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Exception\InvalidRecipientException;
use ColocMatching\CoreBundle\Exception\InvitationNotFoundException;
use ColocMatching\CoreBundle\Exception\UnavailableInvitableException;
use ColocMatching\CoreBundle\Form\Type\Invitation\InvitationType;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Invitation\InvitationRepository;
use ColocMatching\CoreBundle\Validator\FormValidator;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * CRUD manager of the entity Invitation
 *
 * @author Dahiorus
 * @deprecated
 */
class InvitationManager implements InvitationManagerInterface
{
    /** @var ObjectManager */
    private $manager;

    /** @var InvitationRepository */
    private $repository;

    /** @var FormValidator */
    private $entityValidator;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, FormValidator $entityValidator,
        LoggerInterface $logger
    )
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->entityValidator = $entityValidator;
        $this->logger = $logger;
    }


    /**
     * @inheritDoc
     */
    public function list(PageableFilter $filter, array $fields = null) : array
    {
        $this->logger->debug("Getting invitations with pagination", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findPage($filter, $fields);
    }


    /**
     * @inheritDoc
     */
    public function countAll() : int
    {
        $this->logger->debug("Counting all invitations");

        return $this->repository->countAll();
    }


    /**
     * @inheritDoc
     */
    public function create(Invitable $invitable, User $recipient, string $sourceType, array $data) : Invitation
    {
        $this->logger->debug("Creating a new invitation", array ("invitable" => $invitable, "recipient" => $recipient,
            "sourceType" => $sourceType, "data" => $data));

        if (!$invitable->isAvailable())
        {
            throw new UnavailableInvitableException($invitable, "The invitable is unavailable");
        }

        if (!$recipient->isEnabled())
        {
            throw new InvalidRecipientException($recipient, "The recipient is not enabled");
        }

        /** @var Invitation $invitation */
        $invitation = $this->entityValidator->validateEntityForm(
            Invitation::create($invitable, $recipient, $sourceType), $data, InvitationType::class, true);

        $this->manager->persist($invitation);
        $this->manager->flush();

        return $invitation;
    }


    /**
     * @inheritDoc
     */
    public function read(int $id, array $fields = null)
    {
        $this->logger->debug("Getting an existing invitation", array ("id" => $id, "fields" => $fields));

        $invitation = $this->repository->findById($id, $fields);

        if (empty($invitation))
        {
            throw new InvitationNotFoundException("id", $id);
        }

        return $invitation;
    }


    /**
     * @inheritDoc
     */
    public function delete(Invitation $invitation)
    {
        $this->logger->debug("Deleting an existing invitation", array ("invitation" => $invitation));

        $this->manager->remove($invitation);
        $this->manager->flush();
    }


    /**
     * @inheritDoc
     */
    public function answer(Invitation $invitation, bool $accepted) : Invitation
    {
        $this->logger->debug("Answering an invitation", array ("invitation" => $invitation, "accepted" => $accepted));

        if ($invitation->getStatus() !== Invitation::STATUS_WAITING)
        {
            throw new InvalidParameterException("invitation", "The invitation was already answered");
        }

        if ($accepted)
        {
            /** @var User $invitee */
            $invitee = $invitation->getRecipient();

            $invitation->getInvitable()->addInvitee($invitee);
            $invitation->setStatus(Invitation::STATUS_ACCEPTED);
            $this->purge($invitation);

            if ($invitation->getSourceType() == Invitation::SOURCE_INVITABLE && $invitee->hasGroup())
            {
                $this->logger->debug("Sending an invitation to all others members of the invitee group");

                $this->inviteMembers($invitee, $invitation->getInvitable());
            }
        }
        else
        {
            $invitation->setStatus(Invitation::STATUS_REFUSED);
        }

        $this->manager->persist($invitation);
        $this->manager->flush();

        return $invitation;
    }


    /**
     * @inheritDoc
     */
    public function listByRecipient(User $recipient, PageableFilter $filter) : array
    {
        $this->logger->debug("Getting invitations of a recipient",
            array ("recipient" => $recipient, "filter" => $filter));

        return $this->repository->findByRecipient($recipient, $filter);
    }


    /**
     * @inheritDoc
     */
    public function countByRecipient(User $recipient) : int
    {
        $this->logger->debug("Counting invitations of a recipient", array ("recipient" => $recipient));

        return $this->repository->countByRecipient($recipient);
    }


    /**
     * @inheritDoc
     */
    public function listByInvitable(Invitable $invitable, PageableFilter $filter) : array
    {
        $this->logger->debug("Getting invitations of an invitable",
            array ("invitable" => $invitable, "filter" => $filter));

        return $this->repository->findByInvitable($invitable, $filter);
    }


    /**
     * @inheritDoc
     */
    public function countByInvitable(Invitable $invitable) : int
    {
        $this->logger->debug("Counting invitations of a recipient", array ("invitable" => $invitable));

        return $this->repository->countByInvitable($invitable);
    }


    /**
     * @inheritDoc
     */
    public function search(InvitationFilter $filter, array $fields = null) : array
    {
        $this->logger->debug("Searching invitations by filtering", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByFilter($filter, $fields);
    }


    /**
     * @inheritDoc
     */
    public function countBy(InvitationFilter $filter) : int
    {
        $this->logger->debug("Counting invitations by filtering", array ("filter" => $filter));

        return $this->repository->countByFilter($filter);
    }


    /**
     * Refused all invitations in a "waiting" state which are different of the accepted invitation
     *
     * @param Invitation $invitation The accepted invitation
     */
    private function purge(Invitation $invitation)
    {
        $filter = new InvitationFilter();
        $filter->setRecipientId($invitation->getRecipient()->getId());
        $filter->setStatus(Invitation::STATUS_WAITING);
        /** @var array<Invitation> $others */
        $others = $this->repository->findAllBy($filter);

        foreach ($others as $other)
        {
            /** @var Invitation $other */
            if ($other !== $invitation)
            {
                $other->setStatus(Invitation::STATUS_REFUSED);
                $this->manager->persist($other);
            }
        }
    }


    /**
     * Invites all other members of the invitee group
     *
     * @param User $invitee The invitee who will join the invitable
     * @param Invitable $invitable The group or the announcement to join
     */
    private function inviteMembers(User $invitee, Invitable $invitable)
    {
        /** @var Collection $members */
        $members = $invitee->getGroup()->getMembers();

        foreach ($members as $member)
        {
            /** @var User $member */
            if ($member !== $invitee)
            {
                $invitation = Invitation::create($invitable, $member, Invitation::SOURCE_INVITABLE);
                $this->manager->persist($invitation);
            }
        }

        $this->logger->debug(sprintf("%d invitations sent", $members->count() - 1));
    }
}
