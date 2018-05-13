<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\HistoricAnnouncementDto;
use ColocMatching\CoreBundle\Entity\Announcement\Comment;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Manager\AbstractDtoManager;
use ColocMatching\CoreBundle\Mapper\Announcement\CommentDtoMapper;
use ColocMatching\CoreBundle\Mapper\Announcement\HistoricAnnouncementDtoMapper;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class HistoricAnnouncementDtoManager extends AbstractDtoManager implements HistoricAnnouncementDtoManagerInterface
{
    /** @var CommentDtoMapper */
    private $commentDtoMapper;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em,
        HistoricAnnouncementDtoMapper $dtoMapper, CommentDtoMapper $commentDtoMapper)
    {
        parent::__construct($logger, $em, $dtoMapper);
        $this->commentDtoMapper = $commentDtoMapper;
    }


    /**
     * @inheritdoc
     */
    public function getComments(HistoricAnnouncementDto $dto, Pageable $pageable = null) : array
    {
        $this->logger->debug("Getting a historic announcement comments",
            array ("announcement" => $dto, "page" => $pageable->getPage(), "size" => $pageable->getSize()));

        /** @var HistoricAnnouncement $entity */
        $entity = $this->repository->find($dto->getId());

        /** @var Comment[] $comments */
        $comments = empty($pageable) ? $entity->getComments()->toArray()
            : $entity->getComments()->slice($pageable->getOffset(), $pageable->getSize());

        return $this->convertEntityListToDto($comments, $this->commentDtoMapper);
    }


    /**
     * @inheritdoc
     */
    public function countComments(HistoricAnnouncementDto $dto) : int
    {
        $this->logger->debug("Counting a historic announcement comments", array ("announcement" => $dto));

        /** @var HistoricAnnouncement $entity */
        $entity = $this->repository->find($dto->getId());

        return $entity->getComments()->count();
    }


    protected function getDomainClass() : string
    {
        return HistoricAnnouncement::class;
    }
}