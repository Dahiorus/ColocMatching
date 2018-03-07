<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\HistoricAnnouncement;
use ColocMatching\CoreBundle\Manager\AbstractDtoManager;
use ColocMatching\CoreBundle\Mapper\Announcement\HistoricAnnouncementDtoMapper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class HistoricAnnouncementDtoManager extends AbstractDtoManager implements HistoricAnnouncementDtoManagerInterface
{
    /** @var HistoricAnnouncementDtoMapper */
    protected $dtoMapper;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em,
        HistoricAnnouncementDtoMapper $dtoMapper)
    {
        parent::__construct($logger, $em, $dtoMapper);
    }


    protected function getDomainClass() : string
    {
        return HistoricAnnouncement::class;
    }
}