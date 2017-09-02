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
 * @SWG\Definition(
 *   definition="ProfilePicture",
 *   allOf={
 *     { "$ref"="#/definitions/Picture" }
 *   }
 * )
 */
class ProfilePicture extends Picture {

    const UPLOAD_DIR = "pictures/users";

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose()
     * @SWG\Property(description="ProfilePicture id", readOnly=true)
     */
    private $id;


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


    public function getId() : int {
        return $this->id;
    }


    public function setId(int $id) {
        $this->id = $id;

        return $this;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Entity\Picture::getUploadDir()
     */
    public function getUploadDir() : string {
        return self::UPLOAD_DIR;
    }

}