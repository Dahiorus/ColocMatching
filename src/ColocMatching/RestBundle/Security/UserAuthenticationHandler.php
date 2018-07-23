<?php

namespace ColocMatching\RestBundle\Security;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidCredentialsException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Security\LoginForm;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Validator\FormValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Authentication handler to check a user credentials
 *
 * @author Dahiorus
 */
class UserAuthenticationHandler
{
    /** @var LoggerInterface */
    private $logger;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserDtoMapper */
    private $userDtoMapper;

    /** @var FormValidator */
    private $formValidator;

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;


    public function __construct(LoggerInterface $logger, UserDtoManagerInterface $userManager,
        UserDtoMapper $userDtoMapper, FormValidator $formValidator, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->logger = $logger;
        $this->userManager = $userManager;
        $this->userDtoMapper = $userDtoMapper;
        $this->formValidator = $formValidator;
        $this->passwordEncoder = $passwordEncoder;
    }


    /**
     * Handles the user authentication
     *
     * @param string $_username The username to check
     * @param string $_rawPassword The user raw password to check
     *
     * @return UserDto The authenticated user
     * @throws InvalidCredentialsException
     * @throws InvalidFormException
     */
    public function handleCredentials(string $_username, string $_rawPassword) : UserDto
    {
        $this->logger->debug("Getting a user by credentials", array ("username" => $_username));

        $data = array ("_username" => $_username, "_password" => $_rawPassword);
        $this->formValidator->validateForm(null, $data, LoginForm::class, true);

        try
        {
            /** @var UserDto $user */
            $user = $this->userManager->findByUsername($_username);
            /** @var User $entity */
            $entity = $this->userDtoMapper->toEntity($user);
            /** @var boolean $isPasswordValid */
            $isPasswordValid = $this->passwordEncoder->isPasswordValid($entity, $_rawPassword);

            if ($user->getStatus() == UserConstants::STATUS_BANNED || !$isPasswordValid)
            {
                throw new InvalidCredentialsException();
            }

            $user->setLastLogin(new \DateTime());
            $user = $this->userManager->update($user, array (), false);

            $this->logger->info("User authenticated", array ("user" => $user));

            return $user;
        }
        catch (EntityNotFoundException $e)
        {
            throw new InvalidCredentialsException();
        }
    }

}
