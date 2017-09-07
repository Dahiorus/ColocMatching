<?php

namespace ColocMatching\CoreBundle\Manager\Invitation;

use ColocMatching\CoreBundle\Entity\Invitation\Invitable;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvitationNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Invitation\InvitationType;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Invitation\InvitationRepository;
use ColocMatching\CoreBundle\Validator\EntityValidator;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * CRUD manager of the entity Invitation
 *
 * @author Dahiorus
 */
class InvitationManager implements InvitationManagerInterface {

    /** @var ObjectManager */
    private $manager;

    /** @var InvitationRepository */
    private $repository;

    /** @var EntityValidator */
    private $entityValidator;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, EntityValidator $entityValidator,
        LoggerInterface $logger
    ) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->entityValidator = $entityValidator;
        $this->logger = $logger;
    }


    /**
     * @inheritDoc
     */
    public function list(PageableFilter $filter, array $fields = null) : array {
        $this->logger->debug("Getting invitations with pagination", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByPageable($filter, $fields);
    }


    /**
     * @inheritDoc
     */
    public function countAll() : int {
        $this->logger->debug("Counting all invitations");

        return $this->repository->count();
    }


    /**
     * @inheritDoc
     */
    public function create(Invitable $invitable, User $recipient, string $sourceType, array $data) : Invitation {
        $this->logger->debug("Creating a new invitation", array ("invitable" => $invitable, "recipient" => $recipient,
            "sourceType" => $sourceType, "data" => $data));

        if (!$invitable->isAvailable() || !$recipient->isEnabled()) {
            throw new UnprocessableEntityHttpException("Cannot create an invitation");
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
    public function read(int $id, array $fields = null) {
        $this->logger->debug("Getting an existing invitation", array ("id" => $id, "fields" => $fields));

        $invitation = $this->repository->findById($id, $fields);

        if (empty($invitation)) {
            throw new InvitationNotFoundException("id", $id);
        }

        return $invitation;
    }


    /**
     * @inheritDoc
     */
    public function delete(Invitation $invitation) {
        $this->logger->debug("Deleting an existing invitation", array ("invitation" => $invitation));

        $this->manager->remove($invitation);
        $this->manager->flush();
    }


    /**
     * @inheritDoc
     */
    public function answer(Invitation $invitation, bool $accepted) : Invitation {
        $this->logger->debug("Answering an invitation", array ("invitation" => $invitation, "accepted" => $accepted));

        if ($invitation->getStatus() !== Invitation::STATUS_WAITING) {
            throw new UnprocessableEntityHttpException("The invitation was already answered");
        }

        if ($accepted) {
            $invitation->getInvitable()->addInvitee($invitation->getRecipient());
            $invitation->setStatus(Invitation::STATUS_ACCEPTED);
            // TODO remove all other invitation on the target user
        }
        else {
            $invitation->setStatus(Invitation::STATUS_REFUSED);
        }

        $this->manager->persist($invitation);
        $this->manager->flush();

        return $invitation;
    }


    /**
     * @inheritDoc
     */
    public function listByRecipient(User $recipient, PageableFilter $filter) : array {
        $this->logger->debug("Getting invitations of a recipient",
            array ("recipient" => $recipient, "filter" => $filter));

        return $this->repository->findByRecipient($recipient, $filter);
    }


    /**
     * @inheritDoc
     */
    public function countByRecipient(User $recipient) : int {
        $this->logger->debug("Counting invitations of a recipient", array ("recipient" => $recipient));

        return $this->repository->countByRecipient($recipient);
    }


    /**
     * @inheritDoc
     */
    public function listByInvitable(Invitable $invitable, PageableFilter $filter) : array {
        $this->logger->debug("Getting invitations of an invitable",
            array ("invitable" => $invitable, "filter" => $filter));

        return $this->repository->findByInvitable($invitable, $filter);
    }


    /**
     * @inheritDoc
     */
    public function countByInvitable(Invitable $invitable) : int {
        $this->logger->debug("Counting invitations of a recipient", array ("invitable" => $invitable));

        return $this->repository->countByInvitable($invitable);
    }


    /**
     * @inheritDoc
     */
    public function search(InvitationFilter $filter, array $fields = null) : array {
        $this->logger->debug("Searching invitations by filtering", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByFilter($filter, $fields);
    }


    /**
     * @inheritDoc
     */
    public function countBy(InvitationFilter $filter) : int {
        $this->logger->debug("Counting invitations by filtering", array ("filter" => $filter));

        return $this->repository->countByFilter($filter);
    }

}
