<?php

namespace App\Core\Manager\Announcement;

use App\Core\DTO\Announcement\HistoricAnnouncementDto;
use App\Core\Entity\Announcement\Comment;
use App\Core\Entity\Announcement\HistoricAnnouncement;
use App\Core\Manager\AbstractDtoManager;
use App\Core\Mapper\Announcement\CommentDtoMapper;
use App\Core\Mapper\Announcement\HistoricAnnouncementDtoMapper;
use App\Core\Repository\Filter\Pageable\Pageable;
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
        $entity = $this->get($dto->getId());

        /** @var Comment[] $comments */
        $comments = empty($pageable) ? $entity->getComments()->toArray()
            : $entity->getComments()->slice($pageable->getOffset(), $pageable->getSize());

        $this->logger->info("Historic announcement comments found", array ("comments" => $comments));

        return $this->convertEntityListToDto($comments, $this->commentDtoMapper);
    }


    /**
     * @inheritdoc
     */
    public function countComments(HistoricAnnouncementDto $dto) : int
    {
        $this->logger->debug("Counting a historic announcement comments", array ("announcement" => $dto));

        /** @var HistoricAnnouncement $entity */
        $entity = $this->get($dto->getId());

        return $entity->getComments()->count();
    }


    protected function getDomainClass() : string
    {
        return HistoricAnnouncement::class;
    }
}