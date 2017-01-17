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
	
	
	public function __construct(ObjectManager $manager, string $entityClass, FormFactoryInterface $formFactory, LoggerInterface $logger) {
		$this->manager = $manager;
		$this->repository = $manager->getRepository($entityClass);
		$this->formFactory = $formFactory;
		$this->logger = $logger;
	}
	

	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
	 */
	public function countAll() : int {
		$this->logger->debug('Count all Announcements');
		
		return $this->repository->count();
	}


	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getAll()
	 */
	public function getAll(AbstractFilter $filter) : array {
		$this->logger->debug(
			sprintf("Get all Announcements [filter : %s]", $filter)
		);
		
		return $this->repository->findByPage($filter);
	}


	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getFields()
	 */
	public function getFields(array $fields, AbstractFilter $filter) : array {
		$this->logger->debug(
			sprintf("Get all Announcements [fields: [%s] | filter: %s]", implode(', ', $fields), $filter)
		);
		 
		return $this->repository->selectFieldsByPage($fields, $filter);
	}


	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getById()
	 */
	public function getById(int $id) {
		$this->logger->debug(
			sprintf("Get an Announcement by id [id: %d]", $id)
		);
		 
		return $this->repository->find($id);
	}


	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getFieldsById()
	 */
	public function getFieldsById(int $id, array $fields) : array {
		$this->logger->debug(
			sprintf("Get an Announcement by id [id: %d | fields: [%s]]", $id, implode(', ', $fields))
		);
		 
		return $this->repository->selectFieldsFromOne($id, $fields);
	}


	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::getByAddress()
	 */
	public function getByAddress(Address $address, AbstractFilter $filter) : array {
		$this->logger->debug(
			sprintf("Get Announcements by Address [address: %s | filter: %s]", $address, $filter)
		);
		
		return $this->repository->findByAddress($address, $filter);
	}


	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::getFieldsByAddress()
	 */
	public function getFieldsByAddress(Address $address, array $fields, AbstractFilter $filter) : array {
		$this->logger->debug(
			sprintf("Get Announcements by Address [address: %s | fields: [%s] | filter : %s]",
				$address, implode(', ', $fields), $filter)
		);
		
		return $this->repository->selectFieldsByAddress($address, $fields, $filter);
	}
	

	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::create()
	 */
	public function create(User $user, array $data) : Announcement {
		$this->logger->debug(
			sprintf("Create a new Announcement for the User [id: %d]", $user->getId())
		);
		 
		/** @var Announcement */
		$announcement = $this->processForm(new Announcement($user), $data, 'POST');
		$user->setAnnouncement($announcement);
		
		$this->manager->persist($announcement);
		$this->manager->merge($user);
		$this->manager->flush();
		
		return $announcement;
	}


	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::update()
	 */
	public function update(Announcement $announcement, array $data) : Announcement {
		$this->logger->debug(
			sprintf("Update the following Announcement [id : %d]", $announcement->getId())
		);
		
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
		$this->logger->debug(
			sprintf("Delete an existing Announcement [id: %d]", $announcement->getId()));
		
		/** @var User */
		$owner = $announcement->getOwner();
		$owner->setAnnouncement(null);
		
		$this->manager->merge($owner);
		$this->manager->remove($announcement);
		$this->manager->flush();
	}
	
	
	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManagerInterface::partialUpdate()
	 */
	public function partialUpdate(Announcement $announcement, array $data) : Announcement {
		$this->logger->debug(
			sprintf("Update (partial) the following Announcement [id: %d]", $announcement->getId())
		);
		
		$updatedAnnouncement = $this->processForm($announcement, $data, 'PATCH');
		
		$this->manager->persist($updatedAnnouncement);
		$this->manager->flush();
		
		return $updatedAnnouncement;
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
	private function processForm(Announcement $announcement, array $data, string $httpMethod, array $options = []) : Announcement {
		/** @var array */
		$fullOptions = array_merge(['method' => $httpMethod], $options);
		/** @var \Symfony\Component\Form\FormInterface */
		$form = $this->formFactory->create(AnnouncementType::class, $announcement, $fullOptions);
		
		$form->submit($data, $httpMethod !== 'PATCH');
		
		if (!$form->isValid()) {
			throw new InvalidFormDataException("Invalid submitted data in the Announcement form", $form->getErrors(true, true));
		}
		
		$announcement->setLastUpdate(new \DateTime());
		
		$this->logger->debug(
			sprintf("Process an Announcement [method: '%s' | announcement: %s]", $httpMethod, $announcement),
			array (
				'data' => $data,
				'method' => $httpMethod
		));
		
		return $announcement;
	}

}