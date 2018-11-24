<?php

namespace App\Tests;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserStatus;
use App\Core\Entity\User\UserType;
use App\Core\Form\Type\User\AdminUserDtoForm;
use App\Core\Form\Type\User\RegistrationForm;
use App\Core\Manager\User\UserDtoManagerInterface;

trait CreateUserTrait
{
    /**
     * Creates a user with the type "search"
     *
     * @param UserDtoManagerInterface $userManager The user manager
     * @param string $email The user e-mail
     * @param string $status [optional] The user status
     *
     * @return UserDto
     * @throws \Exception
     */
    public function createSearchUser(UserDtoManagerInterface $userManager, string $email,
        string $status = null) : UserDto
    {
        return $this->registerUser($userManager, array (
            "email" => $email,
            "plainPassword" => "Secret&1234",
            "firstName" => "Search",
            "lastName" => "Test",
            "type" => UserType::SEARCH), $status);
    }


    /**
     * Creates a user with the type "proposal"
     *
     * @param UserDtoManagerInterface $userManager The user manager
     * @param string $email The user e-mail
     * @param string $status [optional] The user status
     *
     * @return UserDto
     * @throws \Exception
     */
    public function createProposalUser(UserDtoManagerInterface $userManager, string $email,
        string $status = null) : UserDto
    {
        return $this->registerUser($userManager, array (
            "email" => $email,
            "plainPassword" => "Secret&1234",
            "firstName" => "Proposal",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL), $status);
    }


    /**
     * Creates an admin user
     *
     * @param UserDtoManagerInterface $userManager The user manager
     *
     * @return UserDto
     * @throws \Exception
     */
    public function createAdmin(UserDtoManagerInterface $userManager) : UserDto
    {
        return $userManager->create(array (
            "email" => "admin@test.fr",
            "plainPassword" => "admin1234",
            "firstName" => "Admin",
            "lastName" => "Test",
            "roles" => array ("ROLE_ADMIN"),
            "status" => UserStatus::ENABLED,
        ), AdminUserDtoForm::class);
    }


    /**
     * Creates an API user
     *
     * @param UserDtoManagerInterface $userManager The user manager
     *
     * @return UserDto
     * @throws \Exception
     */
    public function createApiUser(UserDtoManagerInterface $userManager)
    {
        return $userManager->create(array (
            "email" => "api@test.fr",
            "plainPassword" => "api12345",
            "firstName" => "Api",
            "lastName" => "Test",
            "roles" => array ("ROLE_API"),
            "status" => UserStatus::ENABLED,
        ), AdminUserDtoForm::class);
    }


    /**
     * Creates a user using the registration FormType
     *
     * @param UserDtoManagerInterface $userManager The user manager
     * @param array $data The user data to persist
     * @param string $status The user status
     *
     * @return UserDto
     * @throws \Exception
     */
    private function registerUser(UserDtoManagerInterface $userManager, array $data, string $status = null)
    {
        $user = $userManager->create($data, RegistrationForm::class);

        if (!empty($status))
        {
            $user = $userManager->updateStatus($user, $status);
        }

        return $user;
    }

}
