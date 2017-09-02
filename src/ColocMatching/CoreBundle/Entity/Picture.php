<?php

namespace ColocMatching\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Document
 *
 * @ORM\MappedSuperclass()
 * @ORM\EntityListeners({ "ColocMatching\CoreBundle\Listener\PictureListener" })
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Picture")
 */
abstract class Picture implements EntityInterface {

    /**
     * The name of the file
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * The uploaded file
     *
     * @var UploadedFile
     *
     * @Assert\Image()
     */
    protected $file;

    /**
     * The last update date
     *
     * @var \DateTime
     *
     * @ORM\Column(name="last_update", type="datetime", nullable=true)
     */
    protected $lastUpdate;


    /**
     * Constructor
     */
    public function __construct() {
    }


    public function getName() {
        return $this->name;
    }


    public function setName(string $name) {
        $this->name = $name;

        return $this;
    }


    public function getFile() {
        return $this->file;
    }


    public function setFile(UploadedFile $file) {
        $this->file = $file;
        $this->setLastUpdate(new \DateTime());

        return $this;
    }


    public function getLastUpdate() {
        return $this->lastUpdate;
    }


    public function setLastUpdate(\DateTime $lastUpdate) {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }


    /**
     * Path of the picture from the directory "web"
     *
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("webPath")
     * @JMS\Type(name="string")
     * @SWG\Property(property="webPath", type="string", readOnly=true)
     *
     * @return string
     */
    public function getWebPath() : string {
        return sprintf("uploads/%s/%s", $this->getUploadDir(), $this->name);
    }


    /**
     * Get the upload directory path for this picture
     * @return string
     */
    public abstract function getUploadDir() : string;

}