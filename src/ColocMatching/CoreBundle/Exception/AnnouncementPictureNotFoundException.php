<?php

namespace ColocMatching\CoreBundle\Exception;

use ColocMatching\CoreBundle\Exception\EntityNotFoundException;

final class AnnouncementPictureNotFoundException extends EntityNotFoundException {


    public function __construct(int $id, \Exception $previous = null, $code = 0) {
        parent::__construct($id, sprintf("No AnnouncementPicture found with the Id %d", $id), $previous, $code);
    }

}