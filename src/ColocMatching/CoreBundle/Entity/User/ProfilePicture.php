<?php

namespace ColocMatching\CoreBundle\Entity\User;

use ColocMatching\CoreBundle\Entity\Common\Document;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * ProfilePicture
 *
 * @ORM\Entity()
 * @ORM\Table(name="profile_picture")
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("ALL")
 */
class ProfilePicture extends Document {
	
	const UPLOAD_DIR = "upload/users/pictures";
	
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @JMS\Expose()
	 */
	private $id;
	
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}
	
	
	public function getId() {
		return $this->id;
	}
	
	
	public function __toString() {
		$lastUpdate = (empty($this->lastUpdate)) ? "" : $this->lastUpdate->format(\DateTime::ISO8601);
		
		return sprintf("ProfilePicture [id: %d, webPath: '%s', lastUpdate: %s]",
			$this->id, $this->getWebPath(), $lastUpdate);
	}
	
	
	/**
	 * @ORM\PrePersist()
	 * @ORM\PreUpdate()
	 */
	public function generatePicturePath() {
		parent::onPreUpload();
	}
	
	
	/**
	 * @ORM\PostPersist()
	 * @ORM\PostUpdate()
	 */
	public function upload() {
		parent::onUpload();
	}
	
	
	/**
	 * @ORM\PostRemove()
	 */
	public function removePicture() {
		parent::onRemove();
	}


	/**
	 * {@inheritDoc}
	 * @see \ColocMatching\CoreBundle\Entity\Common\Document::getAbsolutePath()
	 */
	protected function getAbsolutePath() : string {
		return sprintf("%s/%s", $this->getAbsoluteUploadDir(), $this->name);
	}


	/**
	 * {@inheritDoc}
	 * @see \ColocMatching\CoreBundle\Entity\Common\Document::getUploadDir()
	 */
	protected function getUploadDir() : string {
		return self::UPLOAD_DIR;
	}

}