<?php

namespace ColocMatching\CoreBundle\DTO;

use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="Picture", allOf={ @SWG\Schema(ref="#/definitions/AbstractDto") })
 */
abstract class PictureDto extends AbstractDto
{
    /**
     * The file name
     * @var string
     */
    protected $name;

    /**
     * The uploaded file
     * @var UploadedFile
     * @Assert\Image()
     */
    protected $file;

    /**
     * The picture web path
     * @var string
     * @Serializer\Expose
     * @Serializer\SerializedName("webPath")
     * @SWG\Property(readOnly=true)
     */
    protected $webPath;


    public function __toString() : string
    {
        return parent::__toString() . "[webPath = '" . $this->webPath . "']";
    }


    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param string $name
     */
    public function setName(?string $name) : void
    {
        $this->name = $name;
    }


    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }


    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null) : void
    {
        $this->file = $file;
    }


    /**
     * @return mixed
     */
    public function getWebPath()
    {
        return $this->webPath;
    }


    /**
     * @param string $webPath
     */
    public function setWebPath(?string $webPath) : void
    {
        $this->webPath = $webPath;
    }

}