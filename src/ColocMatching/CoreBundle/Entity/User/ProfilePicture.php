<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * ProfilePicture
 *
 * @ORM\Entity()
 * @ORM\Table(name="profile_picture")
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="ProfilePicture", allOf={ @SWG\Schema(ref="#/definitions/Picture") })
 */
class ProfilePicture extends Picture {

    const UPLOAD_DIR = "pictures/users";


    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }


    public function __toString() {
        $lastUpdate = (empty($this->lastUpdate)) ? "" : $this->lastUpdate->format(\DateTime::ISO8601);

        return sprintf("ProfilePicture [id: %d, webPath: '%s', lastUpdate: %s]", $this->id, $this->getWebPath(),
            $lastUpdate);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Entity\Picture::getUploadDir()
     */
    public function getUploadDir() : string {
        return self::UPLOAD_DIR;
    }

}