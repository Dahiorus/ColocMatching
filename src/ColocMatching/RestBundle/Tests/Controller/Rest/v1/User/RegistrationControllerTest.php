<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\User;

use ColocMatching\CoreBundle\DAO\UserTokenDao;
use ColocMatching\CoreBundle\DTO\User\UserTokenDto;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Entity\User\UserToken;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserTokenDtoManagerInterface;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends AbstractControllerTest
{
    /** @var UserTokenDtoManagerInterface */
    private $userTokenManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserTokenDto */
    private $userToken;


    protected function initServices() : void
    {
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
        $this->userTokenManager = self::getService("coloc_matching.core.user_token_dto_manager");
    }


    protected function initTestData() : void
    {
        $user = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "password",
            "type" => "proposal",
            "firstName" => "User",
            "lastName" => "Test"
        ));
        $this->userToken = $this->userTokenManager->create($user, UserToken::REGISTRATION_CONFIRMATION);

        self::$client = self::initClient();
    }


    protected function clearData() : void
    {
        $this->userManager->deleteAll();

        /** @var UserTokenDao $userTokenDao */
        $userTokenDao = self::getService("coloc_matching.core.user_token_dao");
        $userTokenDao->deleteAll();
        $userTokenDao->flush();
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function confirmUserRegistrationShouldReturn200()
    {
        self::$client->request("POST", "/rest/registrations/confirmation",
            array ("value" => $this->userToken->getToken()));
        self::assertStatusCode(Response::HTTP_OK);

        $user = $this->userManager->findByUsername($this->userToken->getUsername());
        self::assertEquals(UserConstants::STATUS_ENABLED, $user->getStatus());
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function confirmUserRegistrationWithEmptyTokenShouldReturn400()
    {
        self::$client->request("POST", "/rest/registrations/confirmation",
            array ("value" => null));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function confirmUserRegistrationWithUnknownTokenShouldReturn400()
    {
        self::$client->request("POST", "/rest/registrations/confirmation",
            array ("value" => "azertyuiop7852"));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }


    /**
     * @test
     *
     * @throws \Exception
     */
    public function confirmUserRegistrationWithInvalidReasonTokenShouldReturn400()
    {
        $user = $this->userManager->findByUsername("user@test.fr");
        $userToken = $this->userTokenManager->create($user, UserToken::LOST_PASSWORD);

        self::$client->request("POST", "/rest/registrations/confirmation",
            array ("value" => $userToken->getToken()));
        self::assertStatusCode(Response::HTTP_BAD_REQUEST);
    }

}
