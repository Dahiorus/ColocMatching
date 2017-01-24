<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Psr\Log\LoggerInterface;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\File\File;
use ColocMatching\CoreBundle\Form\Type\DocumentType;
use ColocMatching\CoreBundle\Entity\User\ProfilePicture;

/**
 * CRUD Manager of entity User
 *
 * @author brondon.ung
 */
class UserManager implements UserManagerInterface {
    /** @var ObjectManager */
    private $manager;

    /** @var UserRepository */
    private $repository;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var PasswordEncoderInterface */
    private $encoder;
    
    /** @var LoggerInterface */
    private $logger;


    public function __construct(
    		ObjectManager $manager,
    		string $entityClass,
    		FormFactoryInterface $formFactory,
    		UserPasswordEncoderInterface $encoder,
    		LoggerInterface $logger) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->formFactory = $formFactory;
        $this->encoder = $encoder;
        $this->logger = $logger;
    }

    
    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getAll()
     */
    public function list(AbstractFilter $filter, array $fields = null) : array {
    	if (!empty($fields)) {
    		$this->logger->debug(
    			sprintf("Get all Users [filter: %s | fields: [%s]]",
    				$filter, implode(", ", $fields)));
    		
    		return $this->repository->selectFieldsByPage($fields, $filter);
    	}
    	
    	$this->logger->debug(sprintf("Get all Users [filter: %s]", $filter));
    	
    	return $this->repository->findByPage($filter);
    }
    

    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getById()
     */
    public function read(int $id, array $fields = null) {
    	if (!empty($fields)) {
    		$this->logger->debug(
    			sprintf("Get a User by id [id: %d | fields: [%s]]",
    				$id, implode(", ", $fields)));
    		
    		return $this->repository->selectFieldsFromOne($id, $fields);
    	}
    	
    	$this->logger->debug(sprintf("Get a User by id [id: %d]", $id));
    	
        return $this->repository->find($id);
    }
    

    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll() : int {
    	$this->logger->debug('Count all Users');
    	
        return $this->repository->count();
    }


	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countBy()
	 */
	public function countBy(Criteria $criteria) : int {
		// TODO: Auto-generated method stub
		return 0;
	}



    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::getByUsername()
     */
    public function getByUsername(string $username) {
    	$this->logger->debug(
    		sprintf("Get a User by username [username: '%s']", $username));
    	
        return $this->repository->findOneBy(array('email' => $username));
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::create()
     */
    public function create(array $data) : User {
		$this->logger->debug(sprintf("Create a new User"));
    	
    	/** @var User */
        $user = $this->processDataForm(new User(), $data, 'POST', ['validation_groups' => ['Create', 'Default']]);

        $this->manager->persist($user);
        $this->manager->flush();
        
        return $user;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::update()
     */
    public function update(User $user, array $data) : User {
    	$this->logger->debug(
    		sprintf("Update the following User [id: %d]", $user->getId()));
    	
    	/** @var User */
        $updatedUser = $this->processDataForm($user, $data, 'PUT', ['validation_groups' => ['FullUpdate', 'Default']]);

        $this->manager->persist($updatedUser);
        $this->manager->flush();
        
        return $updatedUser;
    }


	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::delete()
	 */
	public function delete(User $user) {
		$this->logger->debug(
			sprintf("Delete the following User [id: %d]", $user->getId()));
		
		$this->manager->remove($user);
		$this->manager->flush();
	}



    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::partialUpdate()
     */
    public function partialUpdate(User $user, array $data) : User {
    	$this->logger->debug(
    		sprintf("Update (partial) the following User [id: %d]", $user->getId()));
    	
        $updatedUser = $this->processDataForm($user, $data, 'PATCH');

        $this->manager->persist($updatedUser);
        $this->manager->flush();
        
        return $updatedUser;
    }
    
    
    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::uploadProfilePicture()
     */
    public function uploadProfilePicture(User $user, File $file) : User {
    	/** @var ProfilePicture */
    	$picture = (empty($user->getPicture())) ? new ProfilePicture() : $user->getPicture();
    	
    	$this->logger->debug(
    		sprintf("Upload a new profile picture for the user [id: %d, picture: %s]", $user->getId(), $picture),
    		["user" => $user, "file" => $file]);
    	
    	/** @var DocumentType */
    	$form = $this->formFactory->create(DocumentType::class, $picture);
    	
    	if (!$form->submit(["file" => $file], false)->isValid()) {
    		throw new InvalidFormDataException("Invalid submitted data in the Document form", $form->getErrors(true, true));
    	}

    	$user->setPicture($picture);
    	
    	$this->manager->persist($picture);
    	$this->manager->persist($user);
    	$this->manager->flush();
    	
    	$this->logger->debug(
    		sprintf("Profile picture uploaded for the user [id: %d, profilePicture: %s]", $user->getId(), $user->getPicture()),
    		["user" => $user, "picture" => $picture]);
    	
    	return $user;
    }


	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::deleteProfilePicture()
	 */
	public function deleteProfilePicture(User $user) {
		$this->logger->debug(
			sprintf("Delete a User's profile picture [id: %d]", $user->getId()),
			["user" => $user]);
		
		/** @var ProfilePicture */
		$picture = $user->getPicture();
		
		if (!empty($picture)) {
			$this->logger->debug(
				sprintf("Profile picture found for the User [user: %s, picture: %s]", $user, $picture),
				["user" => $user, "picture"=> $picture]);
			
			$this->manager->remove($picture);
			$this->manager->flush();
		}
	}



    /**
     * Process the data in the user validation form.
     *
     * @param User $user
     * @param array $data
     * @param string $httpMethod
     * @param array $options
     * @return User
     * @throws InvalidFormDataException
     */
    private function processDataForm(User $user, array $data, string $httpMethod, array $options = []) : User {
        /** @var array */
        $fullOptions = array_merge(['method' => $httpMethod], $options);
        /** @var \Symfony\Component\Form\FormInterface */
        $form = $this->formFactory->create(UserType::class, $user, $fullOptions);
       
        $form->submit($data, $httpMethod !== 'PATCH');
        
        if (!$form->isValid()) {
            throw new InvalidFormDataException("Invalid submitted data in the User form", $form->getErrors(true, true));
        }
        
        $this->logger->debug(
        	sprintf("Process a User [method: '%s' | user: %s]", $httpMethod, $user),
        	array (
        		'data' => $data,
        		'method' => $httpMethod));

        $password = $this->encoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        
        return $user;
    }
    
}
