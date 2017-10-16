<?php

namespace ColocMatching\CoreBundle\Exception;

use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;

/**
 * Exception thrown when no announcement picture is found by the specified attribute name
 *
 * @author Dahiorus
 */
final class AnnouncementPictureNotFoundException extends EntityNotFoundException {

    /**
     * Constructor
     *
     * @param string $name The name of the attribute on which the exception would be thrown
     * @param mixed $value The value of the attribute
     */
    public function __construct(string $name, $value) {
        parent::__construct(AnnouncementPicture::class, $name, $value);
    }

}