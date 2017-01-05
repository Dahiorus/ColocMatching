<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\CoreBundle\Form\Type\User\UserType;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Entity\User\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
/**
 * Description of UserManager
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


    public function __construct(ObjectManager $manager, string $entityClass, FormFactoryInterface $formFactory, UserPasswordEncoderInterface $encoder) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
        $this->formFactory = $formFactory;
        $this->encoder = $encoder;
    }

    
    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getWithPagination()
     */
    public function getAll(int $page, int $maxResults, string $orderBy, string $sort) {
    	return $this->repository->findWithPagination(($page-1) * $maxResults, $maxResults, $orderBy, $sort);
    }
    

    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getById()
     */
    public function getById(int $id) {
        return $this->repository->find($id);
    }
    

    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getFieldsWithPagination()
     */
    public function getFields(array $fields, int $page, int $maxResults, string $orderBy, string $sort) {
        return $this->repository->selectFieldsWithPagination($fields, ($page-1) * $maxResults, $maxResults, $orderBy, $sort);
    }
    
    
    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::getFieldsById()
     */
    public function getFieldsById(int $id, array $fields) {
    	return $this->repository->selectFieldsFromOne($id, $fields);
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\ManagerInterface::countAll()
     */
    public function countAll() {
        return $this->repository->countAll();
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::getByUsername()
     */
    public function getByUsername(string $username) {
        return $this->repository->findBy(array('username' => $username));
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::create()
     */
    public function create(array $data) {
        $user = $this->processDataForm(new User(), $data, 'POST', ['validation_groups' => ['Create']]);

        $this->manager->persist($user);
        $this->manager->flush();
        
        return $user;
    }


    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::update()
     */
    public function update(User $user, array $data) {
        $updatedUser = $this->processDataForm($user, $data, 'PUT', ['validation_groups' => ['FullUpdate']]);

        $this->manager->persist($updatedUser);
        $this->manager->flush();
        
        return $updatedUser;
    }


	/**
	 * {@inheritdoc}
	 * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::delete()
	 */
	public function delete(User $user) {
		$this->manager->remove($user);
		$this->manager->flush();
	}



    /**
     * {@inheritDoc}
     * @see \ColocMatching\CoreBundle\Manager\User\UserManagerInterface::partialUpdate()
     */
    public function partialUpdate(User $user, array $data) {
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
    private function processDataForm(User $user, array $data, string $httpMethod, array $options = []) {
        /** @var array */
        $fullOptions = array_merge(['method' => $httpMethod], $options);
        /** @var \Symfony\Component\Form\FormInterface */
        $form = $this->formFactory->create(UserType::class, $user, $fullOptions);
       
        $form = $form->submit($data, $httpMethod !== 'PATCH');
        
        if (!$form->isValid()) {
            throw new InvalidFormDataException("Invalid submitted data in the User form", $form->getErrors(true, true));
        }

        $submittedUser = $form->getData();
        $password = $this->encoder->encodePassword($submittedUser, $submittedUser->getPlainPassword());
        $submittedUser->setPassword($password);
        
        return $submittedUser;
    }
}
