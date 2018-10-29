<?php

namespace App\Core\Manager\Announcement;

use App\Core\DTO\Announcement\HistoricAnnouncementDto;
use App\Core\DTO\Collection;
use App\Core\DTO\Page;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Manager\DtoManagerInterface;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\ORMException;

interface HistoricAnnouncementDtoManagerInterface extends DtoManagerInterface
{
    /**
     * Gets a historic announcement comments with paging
     *
     * @param HistoricAnnouncementDto $dto The historic announcement
     * @param Pageable $pageable [optional] Paging information
     *
     * @return Collection|Page
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getComments(HistoricAnnouncementDto $dto, Pageable $pageable = null);


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
