<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Exception\HistoricAnnouncementNotFoundException;
use ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Announcement\HistoricAnnouncementRepository;
use ColocMatching\CoreBundle\Repository\Filter\HistoricAnnouncementFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * CRUD manager of the entity HistoricAnnouncement
 *
 * @author Dahiorus
 */
class HistoricAnnouncementManager implements HistoricAnnouncementManagerInterface {

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var HistoricAnnouncementRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, LoggerInterface $logger) {
        $this->manager = $manager;
        $this->repository = $this->manager->getRepository($entityClass);
        $this->logger = $logger;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface::create()
     */
    public function create(Announcement $announcement): HistoricAnnouncement {
        $this->logger->debug("Creating a new historic announcement", array ("announcement" => $announcement));

        $historicAnnouncement = new HistoricAnnouncement($announcement);

        $this->manager->persist($historicAnnouncement);
        $this->manager->flush();

        return $historicAnnouncement;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::list()
     */
    public function list(PageableFilter $filter, array $fields = null): array {
        if (!empty($fields)) {
            $this->logger->debug("Listing historic announcements", array ("filter" => $filter, "fields" => $fields));

            return $this->repository->selectFieldsByPage($fields, $filter);
        }

        $this->logger->debug("Listing historic announcements", array ("filter" => $filter));

        return $this->repository->findByPage($filter);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll(): int {
        $this->logger->debug("Counting all historic announcements");

        return $this->repository->count();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::read()
     */
    public function read(int $id, array $fields = null) {
        /** @var HistoricAnnouncement */
        $historicAnnouncement = null;

        if (!empty($fields)) {
            $this->logger->debug("Getting a historic announcement by id", array ("id" => $id, "fields" => $fields));

            $historicAnnouncement = $this->repository->selectFieldsFromOne($id, $fields);
        }
        else {
            $this->logger->debug("Getting a historic announcement by id", array ("id" => $id));

            $historicAnnouncement = $this->repository->find($id);
        }

        if (empty($historicAnnouncement)) {
            throw new HistoricAnnouncementNotFoundException("id", $id);
        }

        return $historicAnnouncement;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface::search()
     */
    public function search(HistoricAnnouncementFilter $filter, array $fields = null): array {
        if (!empty($fields)) {
            $this->logger->debug("Searching historic announcements", array ("filter" => $filter, "fields" => $fields));

            return $this->repository->selectFieldsByFilter($fields, $filter);
        }

        $this->logger->debug("Searching historic announcements", array ("filter" => $filter));

        return $this->repository->findByFilter($filter);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\HistoricAnnouncementManagerInterface::countBy()
     */
    public function countBy(HistoricAnnouncementFilter $filter): int {
        $this->logger->debug("Counting historic announcements by filtering", array ("filter" => $filter));

        return $this->repository->countByFilter($filter);
    }

}