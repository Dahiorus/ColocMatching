<?php

namespace ColocMatching\CoreBundle\Entity\Group;

use ColocMatching\CoreBundle\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * GroupPicture
 *
 * @ORM\Entity()
 * @ORM\Table(name="group_picture")
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="GroupPicture", allOf={ @SWG\Schema(ref="#/definitions/Picture") })
 */
class GroupPicture extends Picture {

    const UPLOAD_DIR = "pictures/groups";


    public function __construct() {
        parent::__construct();
    }


    public function __toString() {
        $lastUpdate = (empty($this->lastUpdate)) ? "" : $this->lastUpdate->format(\DateTime::ISO8601);

        return sprintf("GroupPicture [id: %d, webPath: '%s', lastUpdate: %s]", $this->id, $this->getWebPath(),
            $lastUpdate);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Entity\Picture::getUploadDir()
     */
    public function getUploadDir() : string {
        return self::UPLOAD_DIR;
    }

}