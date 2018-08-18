<?php

namespace App\Core\Listener;

use App\Core\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Entity listener to update a user password if set
 *
 * @author Dahiorus
 */
class UserListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;


    public function __construct(LoggerInterface $logger, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->logger = $logger;
        $this->passwordEncoder = $passwordEncoder;
    }


    /**
     * Set the new encoded password before the persist/merge call
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @param User $entity
     */
    public function encodePassword(User $entity)
    {
        if (empty($entity->getPlainPassword()))
        {
            return;
        }

        $this->logger->debug("Setting a new password to a user", array ("user" => $entity));

        $newPassword = $this->passwordEncoder->encodePassword($entity, $entity->getPlainPassword());
        $entity->setPassword($newPassword);
    }

}
