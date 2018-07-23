<?php

namespace ColocMatching\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Deleting announcement event
 *
 * @author Dahiorus
 */
class DeleteAnnouncementEvent extends Event
{
    const DELETE_EVENT = "coloc_matching.announcement.deleted";

    /**
     * @var integer
     */
    private $announcementId;


    /**
     * DeleteAnnouncementEvent constructor.
     *
     * @param int $announcementId
     */
    public function __construct(int $announcementId)
    {
        $this->announcementId = $announcementId;
    }


    public function __toString()
    {
        return "DeleteAnnouncementEvent[announcementId = " . $this->announcementId . "]";
    }


    public function getAnnouncementId() : int
    {
        return $this->announcementId;
    }

}
