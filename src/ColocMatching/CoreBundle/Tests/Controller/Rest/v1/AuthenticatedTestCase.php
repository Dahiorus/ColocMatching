<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use Symfony\Bundle\FrameworkBundle\Client;
use ColocMatching\CoreBundle\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Psr\Log\LoggerInterface;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use Symfony\Component\Form\FormInterface;

class AuthenticatedTestCase extends WebTestCase {

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userManager;


    protected function setUp() {
        $this->client = parent::createClient();
        $this->client->setServerParameter("HTTP_HOST", "coloc-matching.api");

        $this->userManager = parent::createMock(UserManager::class);
        $this->client->getKernel()->getContainer()->set("coloc_matching.core.user_manager", $this->userManager);

        $this->logger = $this->client->getKernel()->getContainer()->get("logger");
    }


    protected function createUser(string $email, string $plainPassword, bool $enabled): User {
        $user = new User();

        $user->setEmail($email);
        $user->setFirstname("User");
        $user->setLastname("Test");
        $user->setPassword(
            $this->client->getKernel()->getContainer()->get("security.password_encoder")->encodePassword($user,
                $plainPassword));
        $user->setEnabled($enabled);

        return $user;
    }


    protected function createFormType(string $class): FormInterface {
        return $this->client->getKernel()->getContainer()->get("form.factory")->create($class);
    }


    protected function mockAuthToken(User $user): string {
        $this->userManager->expects($this->any())->method("findByUsername")->with($user->getUsername())->willReturn(
            $user);
        return $this->client->getKernel()->getContainer()->get("lexik_jwt_authentication.encoder")->encode(
            [ "username" => $user->getUsername()]);
    }

}