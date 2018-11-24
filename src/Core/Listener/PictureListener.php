<?php

namespace App\Core\Listener;

use App\Core\Entity\Announcement\AnnouncementPicture;
use App\Core\Entity\Picture;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;

class PictureListener
{
    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $uploadDirectoryPath;


    public function __construct(LoggerInterface $logger, string $directoryPath)
    {
        $this->logger = $logger;
        $this->uploadDirectoryPath = $directoryPath;
    }


    /**
     * Prepares the name of the document and remove the old file if it exists
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     *
     * @param Picture $picture
     */
    public function createDocumentPath(Picture $picture)
    {
        $this->logger->debug("Setting the name of the picture [{picture}]", array ("picture" => $picture));

        if (!empty($picture->getFile()))
        {
            if (!empty($picture->getName()) && file_exists($this->getRealPath($picture)))
            {
                $this->logger->debug("A file is linked to the picture, removing the link [{picturePath}]",
                    array ("picturePath" => $this->getRealPath($picture)));

                unlink($this->getRealPath($picture));
            }

            $picture->setName(sprintf("%s.%s", sha1(uniqid(mt_rand(), true)), $picture->getFile()->guessExtension()));
        }

        $this->logger->debug("Picture [{picture}] name set", array ("picture" => $picture));
    }


    /**
     * Uploads the file
     *
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     *
     * @param Picture $picture
     */
    public function uploadFile(Picture $picture)
    {
        $this->logger->debug("Uploading the file of the picture [{picture}]", array ("picture" => $picture));

        if (!empty($picture->getFile()))
        {
            $picture->getFile()->move($this->getRealDirectoryPath($picture), $picture->getName());
        }

        $this->logger->debug("Picture file uploaded in the path [{path}]",
            array ("path" => $this->getRealPath($picture)));
    }


    /**
     * Remove the file before the picture deletion
     *
     * @ORM\PostRemove
     *
     * @param Picture $picture
     */
    public function removeFile(Picture $picture)
    {
        $this->logger->debug("Deleting the file linked to the picture [{picture}]", array ("picture" => $picture));

        if (file_exists($this->getRealPath($picture)))
        {
            unlink($this->getRealPath($picture));
        }

        if ($picture instanceof AnnouncementPicture)
        {
            $directoryPath = $this->getRealDirectoryPath($picture);
            $fileCount = count(glob($directoryPath . "/*"));

            if (is_dir($directoryPath) && ($fileCount == 0))
            {
                $this->logger->debug("The directory [{directoryPath}] is empty, removing it",
                    array ("directoryPath" => $directoryPath));

                rmdir($this->getRealDirectoryPath($picture));
            }
        }

        $this->logger->debug("Picture file removed from the path [{path}]",
            array ("path" => $this->getRealPath($picture)));
    }


    /**
     * Gets the absolute picture file path
     *
     * @param Picture $picture The profile picture
     *
     * @return string The absolute picture file path
     */
    private function getRealPath(Picture $picture) : string
    {
        return sprintf("%s/%s", $this->getRealDirectoryPath($picture), $picture->getName());
    }


    /**
     * Gets the absolute picture upload directory path
     *
     * @param Picture $picture The profile picture
     *
     * @return string The absolute picture upload directory path
     */
    private function getRealDirectoryPath(Picture $picture)
    {
        return sprintf("%s/%s", realpath($this->uploadDirectoryPath), $picture->getUploadDir());
    }
}