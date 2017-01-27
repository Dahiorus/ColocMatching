<?php

namespace ColocMatching\CoreBundle\Manager\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Address;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Announcement\AnnouncementType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface;
use ColocMatching\CoreBundle\Repository\Announcement\AnnouncementRepository;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\File\File;
use ColocMatching\CoreBundle\Entity\Announcement\AnnouncementPicture;
use ColocMatching\CoreBundle\Form\Type\DocumentType;

/**
 * CRUD Manager of entity Announcement
 *
 * @author brondon.ung
 */
class AnnouncementManager implements AnnouncementManagerInterface {

    /** @var ObjectManager */
    private $manager;

    /** @var AnnouncementRepository */
    private $repository;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(ObjectManager $manager, string $entityClass, FormFactoryInterface $formFactory, 
        LoggerInterface $logger) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->formFactory = $formFactory;
        $this->logger = $logger;
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll(): int {
        $this->logger->debug('Count all Announcements');
        
        return $this->repository->count();
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::countByAddress()
     */
    public function countByAddress(Address $address): int {
        $this->logger->debug(sprintf("Count all announcements by address [address: %s]", $address));
        
        return $this->repository->countByAddress($address);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countBy()
     */
    public function countBy(Criteria $criteria): int {
        // TODO: Auto-generated method stub
        return 0;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getWithPagination()
     */
    public function list(AbstractFilter $filter, array $fields = null): array {
        if (!empty($fields)) {
            $this->logger->debug(
                sprintf("Get all Announcements [filter: %s | fields: [%s]]", $filter, implode(", ", $fields)));
            
            return $this->repository->selectFieldsByPage($fields, $filter);
        }
        
        $this->logger->debug(sprintf("Get all Announcement [filter: %s]", $filter));
        
        return $this->repository->findByPage($filter);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getById()
     */
    public function read(int $id, array $fields = null) {
        if (!empty($fields)) {
            $this->logger->debug(
                sprintf("Get an Announcement by id [id: %d | fields: [%s]]", $id, implode(", ", $fields)));
            
            return $this->repository->selectFieldsFromOne($id, $fields);
        }
        
        $this->logger->debug(sprintf("Get a User by id [id: %d]", $id));
        
        return $this->repository->find($id);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::getByAddress()
     */
    public function getByAddress(Address $address, AbstractFilter $filter, array $fields = null): array {
        if (!empty($fields)) {
            $this->logger->debug(
                sprintf("Get Announcements by Address [address: %s | fields: [%s] | filter : %s]", $address, 
                    implode(', ', $fields), $filter));
            
            return $this->repository->selectFieldsByAddress($address, $fields, $filter);
        }
        
        $this->logger->debug(sprintf("Get Announcements by Address [address: %s | filter: %s]", $address, $filter));
        
        return $this->repository->findByAddress($address, $filter);
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::create()
     */
    public function create(User $user, array $data): Announcement {
        $this->logger->debug(sprintf("Create a new Announcement for the User [id: %d]", $user->getId()));
        
        /** @var Announcement */
        $announcement = $this->processForm(new Announcement($user), $data, 'POST');
        $user->setAnnouncement($announcement);
        
        $this->manager->persist($announcement);
        $this->manager->persist($user);
        $this->manager->flush();
        
        return $announcement;
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::update()
     */
    public function update(Announcement $announcement, array $data): Announcement {
        $this->logger->debug(sprintf("Update the following Announcement [id : %d]", $announcement->getId()));
        
        $updatedAnnouncement = $this->processForm($announcement, $data, 'PUT');
        
        $this->manager->persist($updatedAnnouncement);
        $this->manager->flush();
        
        return $updatedAnnouncement;
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::delete()
     */
    public function delete(Announcement $announcement) {
        $this->logger->debug(sprintf("Delete an existing Announcement [id: %d]", $announcement->getId()));
        
        $this->manager->remove($announcement);
        $this->manager->flush();
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::partialUpdate()
     */
    public function partialUpdate(Announcement $announcement, array $data): Announcement {
        $this->logger->debug(sprintf("Update (partial) the following Announcement [id: %d]", $announcement->getId()));
        
        $updatedAnnouncement = $this->processForm($announcement, $data, 'PATCH');
        
        $this->manager->persist($updatedAnnouncement);
        $this->manager->flush();
        
        return $updatedAnnouncement;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::uploadAnnouncementPicture()
     */
    public function uploadAnnouncementPicture(Announcement $announcement, File $file): Announcement {
        /** @var AnnouncementPicture */
        $picture = new AnnouncementPicture($announcement);
        
        $this->logger->debug(sprintf("Upload a new picture for an Announcement [id: %d]", $announcement->getId()), 
            [ "announcement" => $announcement, "file" => $file
            ]);
        
        /** @var DocumentType */
        $form = $this->formFactory->create(DocumentType::class, $picture, 
            array ("data_class" => AnnouncementPicture::class, "allow_extra_fields" => true
            ));
        
        if (!$form->submit([ "file" => $file, true
        ])->isValid()) {
            $this->logger->error(sprintf("DocumentType validation error [id: %d]", $announcement->getId()), 
                [ "announcement" => $announcement, "file" => $file, "form" => $form
                ]);
            
            throw new InvalidFormDataException("Invalid submitted data in the Document form", 
                $form->getErrors(true, true));
        }
        
        $announcement->addPicture($picture);
        $announcement->setLastUpdate(new \DateTime());
        
        $this->manager->persist($picture);
        $this->manager->persist($announcement);
        $this->manager->flush();
        
        $this->logger->debug(
            sprintf("New picture uploaded for the announcement [id: %d, announcementPicture: %s]", 
                $announcement->getId(), $picture), [ "announcement" => $announcement, "picture" => $picture
            ]);
        
        return $announcement;
    }


    /**
     * {@inheritdoc}
     * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::deleteAnnouncementPicture()
     */
    public function deleteAnnouncementPicture(AnnouncementPicture $picture) {
        /** @var Announcement */
        $announcement = $picture->getAnnouncement();
        
        $this->logger->debug(
            sprintf("Delete a picture of an existing announcement [announcmeentId: %d, pictureId: %d]", 
                $announcement->getId(), $picture->getId()), [ "announcement" => $announcement, "picture" => $picture
            ]);
        
        $announcement->removePicture($picture);
        
        $this->manager->remove($picture);
        $this->manager->persist($announcement);
        $this->manager->flush();
    }


    /**
     * Process the data in the announcement validation form
     *
     * @param Announcement $announcement
     * @param array $data
     * @param string $httpMethod
     * @param array $options
     * @return Announcement
     */
    private function processForm(Announcement $announcement, array $data, string $httpMethod, array $options = []): Announcement {
        /** @var array */
        $fullOptions = array_merge([ 'method' => $httpMethod
        ], $options);
        /** @var \Symfony\Component\Form\FormInterface */
        $form = $this->formFactory->create(AnnouncementType::class, $announcement, $fullOptions);
        
        $form->submit($data, $httpMethod !== 'PATCH');
        
        if (!$form->isValid()) {
            throw new InvalidFormDataException("Invalid submitted data in the Announcement form", 
                $form->getErrors(true, true));
        }
        
        $announcement->setLastUpdate(new \DateTime());
        
        $this->logger->debug(
            sprintf("Process an Announcement [method: '%s' | announcement: %s]", $httpMethod, $announcement), 
            array ('data' => $data, 'method' => $httpMethod
            ));
        
        return $announcement;
    }

}