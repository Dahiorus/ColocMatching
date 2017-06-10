<?php

namespace ColocMatching\CoreBundle\Tests\Controller\Rest\v1;

use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Manager\User\UserManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

class RestTestCase extends WebTestCase {

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userManager;

    /**
     * @var string
     */
    protected $dateFormat = "d/m/Y";


    protected function setUp() {
        $this->client = parent::createClient();
        $this->client->setServerParameter("HTTP_HOST", "coloc-matching.api");

        $this->userManager = parent::createMock(UserManager::class);
        $this->client->getKernel()->getContainer()->set("coloc_matching.core.user_manager", $this->userManager);
    }


    protected function getForm(string $class): FormInterface {
        return $this->client->getKernel()->getContainer()->get("form.factory")->create($class);
    }


    protected function setAuthenticatedRequest(User $user) {
        $this->client->setServerParameter("HTTP_AUTHORIZATION", sprintf("Bearer %s", $this->mockAuthToken($user)));
    }


    protected function mockAuthToken(User $user): string {
        $this->userManager->expects($this->any())->method("findByUsername")->with($user->getUsername())->willReturn(
            $user);
        return $this->client->getKernel()->getContainer()->get("lexik_jwt_authentication.encoder")->encode(
            array ("username" => $user->getUsername()));
    }


    protected function getResponseContent(): array {
        $response = $this->client->getResponse();
        $data = [ ];

        $data["code"] = $response->getStatusCode();

        if (!empty($response->getContent())) {
            $data["rest"] = json_decode($response->getContent(), true);
        }

        return $data;
    }


    protected static function createTempFile(string $filepath, string $filename): File {
        $file = tempnam(sys_get_temp_dir(), "tst");
        imagejpeg(imagecreatefromjpeg($filepath), $file);

        return new UploadedFile($file, $filename, "image/jpeg", null, null, true);
    }

}