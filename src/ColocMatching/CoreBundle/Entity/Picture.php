<?php

namespace ColocMatching\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Document
 *
 * @ORM\MappedSuperclass
 * @ORM\EntityListeners({
 *   "ColocMatching\CoreBundle\Listener\UpdateListener",
 *   "ColocMatching\CoreBundle\Listener\PictureListener",
 *   "ColocMatching\CoreBundle\Listener\CacheDriverListener"
 * })
 */
abstract class Picture extends AbstractEntity implements EntityInterface
{
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
     * Picture constructor.
     *
     * @param UploadedFile|null $file
     */
    public function __construct(UploadedFile $file = null)
    {
        $this->file = $file;
    }


    public function __toString()
    {
        return parent::__toString() . "[webPath = " . $this->getWebPath() . "]";
    }


    public function getName()
    {
        return $this->name;
    }


    public function setName(?string $name)
    {
        $this->name = $name;

        return $this;
    }


    public function getFile()
    {
        return $this->file;
    }


    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        $this->setLastUpdate(new \DateTime());

        return $this;
    }


    /**
     * Path of the picture from the directory "web"
     *
     * @return string
     */
    public function getWebPath() : string
    {
        return sprintf("/uploads/%s/%s", $this->getUploadDir(), $this->name);
    }


    /**
     * Get the upload directory path for this picture
     * @return string
     */
    public abstract function getUploadDir() : string;

}