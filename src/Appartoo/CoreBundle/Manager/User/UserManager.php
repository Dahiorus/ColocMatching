<?php

namespace Appartoo\CoreBundle\Manager\User;

use Appartoo\CoreBundle\Repository\User\UserRepository;
use Appartoo\CoreBundle\Manager\ManagerInterface;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of UserManager
 *
 * @author brondon.ung
 */
class UserManager implements ManagerInterface {
    /** @var ObjectManager */
    private $manager;
    
    /** @var UserRepository */
    private $repository;
    
    public function __construct(ObjectManager $manager, string $entityClass) {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($entityClass);
    }
    
    
    /**
     * Find all users from the database
     * 
     * @return array of User
     */
    public function getAll(): array {
        return $this->repository->findAll();
    }
    
    
    public function getWithPagination(int $page, int $maxResults) {
        return $this->repository->findWithPagination(($page-1) * $maxResults, $maxResults);
    }
    
    
    public function getBy(array $criteria) {
        return $this->repository->findBy($criteria);
    }
    
    
    /**
     * Find a user by its id
     * 
     * @param int $id
     * @return User 
     */
    public function getById(int $id): User {
        return $this->repository->find($id);
    }
    
    
    public function create(User $user) {
        $this->manager->persist($user);
        $this->manager->flush();
    }
    
    
    public function update(User $user) {
        
    }
    
    
    public function delete(User $user) {
        $this->manager->remove($user);
        $this->manager->flush();
    }
    
    
    public function countAll() {
        return $this->repository->countAll();
    }
    
    
    public function getByUsername(string $username): User {
        return $this->repository->findBy(array('username' => $username));
    }
    
    
    private function updatePassword() {
        
    }
}
