<?php

namespace ColocMatching\CoreBundle\Listener;

use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;

class PictureListener {

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $uploadDirectoryPath;


    /**
     * DocumentListener constructor.
     *
     * @param string $directoryPath
     */
    public function __construct(string $directoryPath, LoggerInterface $logger) {
        $this->uploadDirectoryPath = $directoryPath;
        $this->logger = $logger;
    }


    /**
     * Prepares the name of the document and remove the old file if it exists
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     *
     * @param Picture $picture
     */
    public function createDocumentPath(Picture $picture) {
        $this->logger->debug("Setting the name of a document", array ("document" => $picture));

        if (!empty($picture->getFile())) {
            if (!empty($picture->getName()) && file_exists($this->getRealPath($picture))) {
                $this->logger->debug("A file is linked to the document, unlinking it");

                unlink($this->getRealPath($picture));
            }

            $picture->setName(sprintf("%s.%s", sha1(uniqid(mt_rand(), true)), $picture->getFile()->guessExtension()));
        }
    }


    /**
     * Uploads the file
     *
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     *
     * @param Picture $picture
     */
    public function uploadFile(Picture $picture) {
        $this->logger->debug("Uploading the file of a document", array ("document" => $picture));

        if (!empty($picture->getFile())) {
            $picture->getFile()->move($this->getRealDirectoryPath($picture), $picture->getName());
        }
    }


    /**
     * Remove the file after the document deletion
     *
     * @ORM\PostRemove()
     *
     * @param Picture $picture
     */
    public function removeFile(Picture $picture) {
        $this->logger->debug("Deleting the file linked to a document");

        if (file_exists($this->getRealPath($picture))) {
            unlink($this->getRealPath($picture));
        }

        if ($picture instanceof AnnouncementPicture) {
            $fileCount = count(glob($this->getRealDirectoryPath($picture) . "/*"));

            if (is_dir($this->getRealDirectoryPath($picture)) && ($fileCount == 0)) {
                $this->logger->debug("The directory is empty, removing it");

                rmdir($this->getRealDirectoryPath($picture));
            }
        }
    }


    private function getRealPath(Picture $picture) : string {
        return sprintf("%s/%s", $this->getRealDirectoryPath($picture), $picture->getName());
    }


    private function getRealDirectoryPath(Picture $picture) {
        return sprintf("%s/%s", realpath($this->uploadDirectoryPath), $picture->getUploadDir());
    }
}