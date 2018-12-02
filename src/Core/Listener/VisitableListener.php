<?php

namespace App\Core\Listener;

use App\Core\Entity\Visit\Visit;
use App\Core\Entity\Visit\Visitable;
use App\Core\Repository\Visit\VisitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;

class VisitableListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var VisitRepository
     */
    private $visitRepository;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->visitRepository = $entityManager->getRepository(Visit::class);
    }


    /**
     * Deletes all visits done on the entity
     *
     * @ORM\PreRemove
     *
     * @param Visitable $entity
     */
    public function deleteVisits(Visitable $entity)
    {
        $entityName = $this->entityManager->getMetadataFactory()->getMetadataFor(get_class($entity))->getName();
        $visits = $this->visitRepository->findBy(array (
            "visitedClass" => $entityName,
            "visitedId" => $entity->getId()
        ));

        if (!empty($visits))
        {
            $this->logger->debug("Deleting all visits done on [{visitable}]", array ("visitable" => $entity));

            $count = $this->visitRepository->deleteEntities($visits);

            $this->logger->debug("{count} visits deleted", array ("count" => $count));
        }
    }

}
