<?php

namespace App\Rest\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Deleting announcement event
 *
 * @author Dahiorus
 */
class DeleteAnnouncementEvent extends Event
{
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
