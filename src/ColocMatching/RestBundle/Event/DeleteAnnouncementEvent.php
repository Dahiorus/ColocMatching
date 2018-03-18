<?php

namespace ColocMatching\RestBundle\Event;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use Symfony\Component\EventDispatcher\Event;

class DeleteAnnouncementEvent extends Event
{
    const DELETE_EVENT = "coloc_matching.announcement.deleted";

    /**
     * @var AnnouncementDto
     */
    private $announcement;

    /**
     * @var UserDto[]
     */
    private $candidates;


    /**
     * DeleteAnnouncementEvent constructor.
     *
     * @param AnnouncementDto $announcement The announcement to delete
     * @param UserDto[] $candidates The announcement candidates to inform
     */
    public function __construct(AnnouncementDto $announcement, array $candidates)
    {
        $this->announcement = $announcement;
        $this->candidates = $candidates;
    }


    public function __toString()
    {
        return "DeleteAnnouncementEvent[announcement = " . $this->announcement . "]";
    }


    public function getAnnouncement() : AnnouncementDto
    {
        return $this->announcement;
    }


    /**
     * @return UserDto[]
     */
    public function getCandidates() : array
    {
        return $this->candidates;
    }
}