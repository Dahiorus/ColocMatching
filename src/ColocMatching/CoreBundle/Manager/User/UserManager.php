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
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getWithPagination()
     */
    public function getAll(AbstractFilter $filter) : array {
    	$this->logger->debug(
    		sprintf("Get all Users [filter : {%s}]", $filter)
    	);
    	
    	return $this->repository->findByPage($filter);
    }
    

    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getById()
     */
    public function getById(int $id) {
    	$this->logger->debug(
    		sprintf("Get a User by id [id : %d]", $id)
		);
    	
        return $this->repository->find($id);
    }
    

    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getFieldsWithPagination()
     */
    public function getFields(array $fields, AbstractFilter $filter) : array {
    	$this->logger->debug(
    		sprintf("Get all Users [fields : [%s] | filter : {%s} ]",
    			implode(', ', $fields), $filter)
    	);
    	
        return $this->repository->selectFieldsByPage($fields, $filter);
    }
    
    
    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getFieldsById()
     */
    public function getFieldsById(int $id, array $fields) {
    	$this->logger->debug(
    		sprintf("Get a User by id [id : %d | fields : [%s]]", $id, implode(', ', $fields))
    	);
    	
    	return $this->repository->selectFieldsFromOne($id, $fields);
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
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::getByUsername()
     */
    public function getByUsername(string $username) {
    	$this->logger->debug(
    		sprintf("Get a User by id [username : '%s']", $username)
    	);
    	
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
    		sprintf("Update the following User [id=%d]", $user->getId())
    	);
    	
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
			sprintf("Delete the following User [id=%d]", $user->getId())
		);
		
		$this->manager->remove($user);
		$this->manager->flush();
	}



    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::partialUpdate()
     */
    public function partialUpdate(User $user, array $data) : User {
    	$this->logger->debug(
    		sprintf("Update (partial) the following User [id=%d]", $user->getId())
    	);
    	
        $updatedUser = $this->processDataForm($user, $data, 'PATCH');

        $this->manager->persist($updatedUser);
        $this->manager->flush();
        
        return $updatedUser;
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
       
        $form = $form->submit($data, $httpMethod !== 'PATCH');
        
        if (!$form->isValid()) {
            throw new InvalidFormDataException("Invalid submitted data in the User form", $form->getErrors(true, true));
        }
        
        $this->logger->debug(
        	sprintf("Process a User [method='%s' | user=%s]", $httpMethod, $user),
        	array (
        		'data' => $data,
        		'method' => $httpMethod
        ));

        $password = $this->encoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        
        return $user;
    }
    
}
