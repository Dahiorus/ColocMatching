<?php

namespace ColocMatching\CoreBundle\Entity\Announcement;

use ColocMatching\CoreBundle\Entity\Common\Document;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * AnnouncementPicture
 *
 * @ORM\Entity()
 * @ORM\Table(name="announcement_picture", indexes={
 *   @ORM\Index(name="announcement_picture_announcement", columns={"announcement_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 * @JMS\ExclusionPolicy("ALL")
 */
class AnnouncementPicture extends Document {
	
	const UPLOAD_ROOT_DIR = "uploads/pictures/announcements";
	
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
	 *
	 * @var Announcement
	 *
	 * @ORM\ManyToOne(targetEntity="Announcement", inversedBy="pictures", cascade={"persist", "remove"}, fetch="LAZY")
	 * @ORM\JoinColumn(name="announcement_id", referencedColumnName="id", nullable=false)
	 */
	private $announcement;
	
	
	public function __construct(Announcement $announcement) {
		$this->announcement = $announcement;
	}
	
	
	public function getId() {
		return $this->id;
	}
	
	
	public function getAnnouncement() {
		return $this->announcement;
	}
	
	
	public function setAnnouncement(Announcement $announcement) {
		$this->announcement = $announcement;
		return $this;
	}
	
	
	public function __toString() {
		return sprintf("AnnouncementPicture [id: %, announcement: %s]",
			$this->id, $this->announcement);
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
		if (!is_dir($this->getAbsoluteUploadDir())) {
			mkdir($this->getAbsoluteUploadDir());
		}
		
		parent::onUpload();
	}
	
	
	/**
	 * @ORM\PostRemove()
	 */
	public function removePicture() {
		parent::onRemove();
	}


	protected function getUploadDir() : string {
		return sprintf("%s/%d", self::UPLOAD_ROOT_DIR, $this->announcement->getId());
	}

}