<?php

namespace App\Tests;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserType;
use App\Core\Form\Type\User\AdminUserDtoForm;
use App\Core\Form\Type\User\RegistrationForm;
use App\Core\Manager\User\UserDtoManagerInterface;

trait CreateUserTrait
{
    /**
     * @param UserDtoManagerInterface $userManager
     * @param string $email
     *
     * @return UserDto
     * @throws \Exception
     */
    public function createSearchUser(UserDtoManagerInterface $userManager, string $email) : UserDto
    {
        return $userManager->create(array (
            "email" => $email,
            "plainPassword" => array (
                "password" => "Secret&1234",
                "confirmPassword" => "Secret&1234"
            ),
            "firstName" => "Search",
            "lastName" => "Test",
            "type" => UserType::SEARCH), RegistrationForm::class);
    }


    /**
     * @param UserDtoManagerInterface $userManager
     * @param string $email
     *
     * @return UserDto
     * @throws \Exception
     */
    public function createProposalUser(UserDtoManagerInterface $userManager, string $email) : UserDto
    {
        return $userManager->create(array (
            "email" => $email,
            "plainPassword" => array (
                "password" => "Secret&1234",
                "confirmPassword" => "Secret&1234"
            ),
            "firstName" => "Proposal",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL), RegistrationForm::class);
    }


    /**
     * @param UserDtoManagerInterface $userManager
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
            "roles" => array ("ROLE_ADMIN")
        ), AdminUserDtoForm::class);
    }


    /**
     * @param UserDtoManagerInterface $userManager
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
            "roles" => array ("ROLE_API")
        ), AdminUserDtoForm::class);
    }
}