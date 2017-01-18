<?php

namespace ColocMatching\CoreBundle\Entity\Common;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Document
 *
 * @JMS\ExclusionPolicy("ALL")
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
	 * @Assert\NotBlank()
	 * @Assert\File()
	 */
	protected $file;
	
	/***
	 * The creation date
	 * @var \DateTime
	 * @ORM\Column(name="created", type="datetime")
	 */
	protected $created;
	
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->created = new \DateTime();
	}


	public function getName() : string {
		return $this->name;
	}


	public function setName(string $name) {
		$this->name = $name;
		return $this;
	}


	public function getFile() : UploadedFile {
		return $this->file;
	}


	public function setFile(UploadedFile $file) {
		$this->file = $file;
		return $this;
	}


	public function getCreated() : \DateTime {
		return $this->created;
	}


	public function setCreated(\DateTime $created) {
		$this->created = $created;
		return $this;
	}
	
	
	/**
	 * Get the path of the document from the directory "web"
	 * @JMS\VirtualProperty()
	 * @JMS\Type(name="string")
	 * @return string
	 */
	public function getWebPath() : string {
		return sprintf("%s/%s", $this->getUploadDir(), $this->name);
	}
	
	
	/**
	 * Action on pre persist/update document
	 */
	protected function onPreUpload() {
		if (!empty($this->file)) {
			$this->setName(
				sprintf("%s.%s", sha1(uniqid(mt_rand(), true)),
					$this->file->guessExtension()));
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
	protected abstract function getAbsolutePath() : string;

	
	/**
	 * Get the upload directory path for this document
	 * @return string
	 */
	protected abstract function getUploadDir() : string;
	
}