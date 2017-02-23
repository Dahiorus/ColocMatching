<?php

namespace ColocMatching\CoreBundle\Entity\Common;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Document
 * @ORM\MappedSuperclass()
 * @JMS\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Document")
 */
abstract class Document {

    /**
     * The name of the file
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * The uploaded file
     * @var UploadedFile
     * @Assert\File()
     */
    protected $file;

    /**
     * The last update date
     * @var \DateTime
     * @ORM\Column(name="last_update", type="datetime", nullable=true)
     */
    protected $lastUpdate;


    /**
     * Constructor
     */
    public function __construct() {
    }


    public function getName(): string {
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
     * Path of the document from the directory "web"
     *
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("webPath")
     * @JMS\Type(name="string")
     * @SWG\Property(property="webPath", type="string", readOnly=true)
     * @return string
     */
    public function getWebPath(): string {
        return sprintf("%s/%s", $this->getUploadDir(), $this->name);
    }


    /**
     * Action on pre persist/update document
     */
    protected function onPreUpload() {
        if (!empty($this->file)) {
            if (!empty($this->name) && file_exists($this->getAbsolutePath())) {
                unlink($this->getAbsolutePath()); // on update, delete old file
            }
            
            $this->setName(sprintf("%s.%s", sha1(uniqid(mt_rand(), true)), $this->file->guessExtension()));
        }
    }


    /**
     * Action on upload document
     */
    protected function onUpload() {
        if (!empty($this->file)) {
            $this->file->move($this->getAbsoluteUploadDir(), $this->name);
            unset($this->file);
        }
    }


    /**
     * Action on remove document
     */
    public function onRemove() {
        if (($this->getAbsolutePath() !== null) && file_exists($this->getAbsolutePath())) {
            unlink($this->getAbsolutePath());
        }
    }


    /**
     * Get the absolute path to the upload directory
     * @return string
     */
    protected function getAbsoluteUploadDir() {
        return sprintf("%s/../../../../../web/%s", __DIR__, $this->getUploadDir());
    }


    /**
     * Get the absolute path of the document
     * @return string
     */
    protected function getAbsolutePath(): string {
        return sprintf("%s/%s", $this->getAbsoluteUploadDir(), $this->name);
    }


    /**
     * Get the upload directory path for this document
     * @return string
     */
    protected abstract function getUploadDir(): string;

}