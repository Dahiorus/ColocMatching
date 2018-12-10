<?php

namespace App\Tests\Core\Security\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserType;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Security\User\JwtEncoder;
use App\Tests\AbstractServiceTest;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class JwtEncoderTest extends AbstractServiceTest
{
    /** @var JwtEncoder */
    private $tokenEncoder;

    /** @var JWTEncoderInterface */
    private $jwtEncoder;

    /** @var MockObject */
    private $userManager;

    /** @var UserDto */
    private $testDto;


    protected function setUp()
    {
        parent::setUp();

        $this->userManager = $this->createMock(UserDtoManagerInterface::class);
        $this->jwtEncoder = $this->getService("lexik_jwt_authentication.encoder");
        $tokenManager = $this->getService("lexik_jwt_authentication.jwt_manager");
        $tokenExtractor = $this->getService("lexik_jwt_authentication.extractor.authorization_header_extractor");

        $this->testDto = $this->mockUser();

        $this->tokenEncoder = new JwtEncoder($this->logger, $this->userManager, $tokenManager, $tokenExtractor);
    }


    private function mockUser() : UserDto
    {
        $data = array (
            "email" => "user@test.fr",
            "plainPassword" => "password",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserType::PROPOSAL);

        $user = new UserDto();
        $user
            ->setId(1)
            ->setEmail($data["email"])
            ->setPlainPassword($data["plainPassword"])
            ->setFirstName($data["firstName"])
            ->setLastName($data["lastName"])
            ->setRoles(array ("ROLE_USER"))
            ->setType($data["type"]);

        $this->userManager->method("findByUsername")
            ->with($data["email"])
            ->willReturn($user);

        return $user;
    }


    /**
     * @test
     * @throws \Exception
     */
    public function encode()
    {
        $token = $this->tokenEncoder->encode($this->testDto);

        self::assertNotEmpty($token, "Expected the token to be not null");

        $payload = $this->jwtEncoder->decode($token);
        self::assertNotEmpty($payload, "Expected the token to have user payload");
        self::assertEquals($this->testDto->getUsername(), $payload["username"]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function decode()
    {
        $token = $this->jwtEncoder->encode(array (
            "username" => $this->testDto->getUsername(),
            "roles" => array ("ROLE_USER", "ROLE_PROPOSAL")));
        $request = Request::create("/rest/groups");
        $request->headers->set("Authorization", "Bearer $token");

        $user = $this->tokenEncoder->decode($request);

        self::assertNotNull($user, "Expected to get the authenticated user");
        self::assertEquals($this->testDto->getUsername(), $user->getUsername());
    }

}
