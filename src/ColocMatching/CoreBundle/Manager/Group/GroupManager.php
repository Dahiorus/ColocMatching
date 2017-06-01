<?php

namespace ColocMatching\CoreBundle\Manager\Group;

use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\GroupNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Group\GroupType;
use ColocMatching\CoreBundle\Repository\Filter\GroupFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Group\GroupRepository;
use ColocMatching\CoreBundle\Validator\EntityValidator;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use Doctrine\Common\Collections\ArrayCollection;

class GroupManager implements GroupManagerInterface {

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var EntityValidator
     */
    private $entityValidator;

    /**
     * @var GroupRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, EntityValidator $entityValidator,
        LoggerInterface $logger) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->entityValidator = $entityValidator;
        $this->logger = $logger;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::list()
     */
    public function list(PageableFilter $filter, array $fields = null): array {
        $this->logger->debug("Listing groups with pagination", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByPageable($filter, $fields);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll(): int {
        $this->logger->debug("Counting all groups");

        return $this->repository->count();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface::create()
     */
    public function create(User $user, array $data): Group {
        $this->logger->debug("Creating a new group", array ("creator" => $user, "data" => $data));

        if (!empty($user->getGroup())) {
            throw new UnprocessableEntityHttpException(
                sprintf("The user '%s' already has a group", $user->getUsername()));
        }

        /** @var Group */
        $group = $this->entityValidator->validateEntityForm(new Group($user), $data, GroupType::class, true);
        $user->setGroup($group);

        $this->manager->persist($group);
        $this->manager->persist($user);
        $this->manager->flush();

        return $group;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::read()
     */
    public function read(int $id, array $fields = null) {
        $this->logger->debug("Getting an existing group", array ("id" => $id, "fields" => $fields));

        /** @var Group */
        $group = $this->repository->findById($id, $fields);

        if (empty($group)) {
            throw new GroupNotFoundException("id", $id);
        }

        return $group;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface::update()
     */
    public function update(Group $group, array $data, bool $clearMissing): Group {
        $this->logger->debug("Updating an existing group",
            array ("group" => $group, "data" => $data, "clearMissing" => $clearMissing));

        /** @var Group */
        $updatedGroup = $this->entityValidator->validateEntityForm($group, $data, GroupType::class, $clearMissing);

        $this->manager->persist($updatedGroup);
        $this->manager->flush();

        return $updatedGroup;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface::delete()
     */
    public function delete(Group $group) {
        $this->logger->debug("Deleting an existing group", array ("group" => $group));

        $this->manager->remove($group);
        $this->manager->flush();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface::search()
     */
    public function search(GroupFilter $filter, array $fields = null): array {
        $this->logger->debug("Searching groups by filtering", array ("filter" => $filter, "fields" => $fields));

        return $this->repository->findByFilter($filter, $fields);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface::countBy()
     */
    public function countBy(GroupFilter $filter): int {
        $this->logger->debug("Counting groups by filtering", array ("filter" => $filter));

        return $this->repository->countByFilter($filter);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface::addMember()
     */
    public function addMember(Group $group, User $user): Collection {
        $this->logger->debug("Adding a new member to an existing group", array ("group" => $group, "user" => $user));

        if ($user->getType() != UserConstants::TYPE_SEARCH) {
            throw new UnprocessableEntityHttpException(
                sprintf("Cannot add a user with the type '%s' to the group", $user->getType()));
        }

        $group->addMember($user);

        $this->manager->persist($group);
        $this->manager->flush();

        return $group->getMembers();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Group\GroupManagerInterface::removeMember()
     */
    public function removeMember(Group $group, int $userId) {
        $this->logger->debug("Removing a member of an existing group", array ("group" => $group, "userId" => $userId));

        if ($userId == $group->getCreator()->getId()) {
            throw new UnprocessableEntityHttpException("Cannot remove the creator of the group");
        }

        /** @var ArrayCollection */
        $members = $group->getMembers();

        foreach ($members as $member) {
            if ($member->getId() == $userId) {
                $this->logger->debug("Member found", array ("group" => $group, "member" => $member));

                $group->removeMember($member);

                $this->manager->persist($group);
                $this->manager->flush();

                return;
            }
        }
    }

}