<?php

namespace App\Tests\Rest\Controller\v1;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserType;
use App\Core\Manager\User\UserDtoManagerInterface;

trait CreateUserTrait
{
    /**
     * @param UserDtoManagerInterface $userManager
     *
     * @return UserDto
     * @throws \Exception
     */
    public function createSearchUser(UserDtoManagerInterface $userManager) : UserDto
    {
        return $userManager->create(array (
            "email" => "search@test.fr",
            "plainPassword" => array (
                "password" => "Secret&1234",
                "confirmPassword" => "Secret&1234"
            ),
            "firstName" => "Search",
            "lastName" => "Test",
            "type" => UserType::SEARCH));
    }


    /**
     * @param UserDtoManagerInterface $userManager
     *
     * @return UserDto
     * @throws \Exception
     */
    public function createProposalUser(UserDtoManagerInterface $userManager) : UserDto
    {
        return $userManager->create(array (
            "email" => "proposal@test.fr",
            "plainPassword" => array (
                "password" => "Secret&1234",
                "confirmPassword" => "Secret&1234"
            ),
            "firstName" => "Proposal",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL));
    }
}