<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\HistoricAnnouncementDto;
use ColocMatching\CoreBundle\Entity\Announcement\Comment;
use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Manager\AbstractDtoManager;
use ColocMatching\CoreBundle\Mapper\Announcement\CommentDtoMapper;
use ColocMatching\CoreBundle\Mapper\Announcement\HistoricAnnouncementDtoMapper;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
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
    public function getComments(HistoricAnnouncementDto $dto, PageableFilter $filter) : array
    {
        $this->logger->debug("Getting a historic announcement comments",
            array ("announcement" => $dto, "page" => $filter->getPage(), "size" => $filter->getSize()));

        /** @var HistoricAnnouncement $entity */
        $entity = $this->get($dto->getId());

        return array_map(function (Comment $comment) {
            return $this->commentDtoMapper->toDto($comment);
        }, $entity->getComments()->slice($filter->getOffset(), $filter->getSize()));
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