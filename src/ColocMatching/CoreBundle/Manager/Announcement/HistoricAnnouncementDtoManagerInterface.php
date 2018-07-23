<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\CommentDto;
use ColocMatching\CoreBundle\DTO\Announcement\HistoricAnnouncementDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Manager\DtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\ORMException;

interface HistoricAnnouncementDtoManagerInterface extends DtoManagerInterface
{
    /**
     * Gets a historic announcement comments with paging
     *
     * @param HistoricAnnouncementDto $dto The historic announcement
     * @param Pageable $pageable [optional] Paging information
     *
     * @return CommentDto[]
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getComments(HistoricAnnouncementDto $dto, Pageable $pageable = null) : array;


    /**
     * Counts a historic announcement comments
     *
     * @param HistoricAnnouncementDto $dto The historic announcement
     *
     * @return int
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function countComments(HistoricAnnouncementDto $dto) : int;

}
