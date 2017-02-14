<?php

namespace ColocMatching\CoreBundle\Exception;

use ColocMatching\CoreBundle\Exception\EntityNotFoundException;

final class AnnouncementNotFoundException extends EntityNotFoundException {


    public function __construct(int $id, \Exception $previous = null, $code = 0) {
        parent::__construct($id, sprintf("No Announcement found with the Id %d", $id), $previous, $code);
    }

}