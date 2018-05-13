<?php

namespace ColocMatching\CoreBundle\Tests\Security\User;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\User\UserDtoManager;
use ColocMatching\CoreBundle\Mapper\User\UserDtoMapper;
use ColocMatching\CoreBundle\Repository\User\UserRepository;
use ColocMatching\CoreBundle\Security\User\JwtEncoder;
use ColocMatching\CoreBundle\Tests\AbstractServiceTest;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Request;

class JwtEncoderTest extends AbstractServiceTest
{
    /** @var JwtEncoder */
    private $tokenEncoder;

    /** @var JWTEncoderInterface */
    private $jwtEncoder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $userManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $userRepository;

    /** @var UserDto */
    private $testDto;


    protected function setUp()
    {
        parent::setUp();

        $this->userManager = $this->createMock(UserDtoManager::class);
        $this->userRepository = $this->createMock(UserRepository::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method("getRepository")->with(User::class)->willReturn($this->userRepository);

        $this->jwtEncoder = $this->getService("lexik_jwt_authentication.encoder");
        $tokenManager = $this->getService("lexik_jwt_authentication.jwt_manager");
        $tokenExtractor = $this->getService("lexik_jwt_authentication.extractor.authorization_header_extractor");

        $this->testDto = $this->mockUser();

        $this->tokenEncoder = new JwtEncoder($this->logger, $this->userManager, $entityManager, $tokenManager,
            $tokenExtractor);
    }


    private function mockUser() : UserDto
    {
        $data = array (
            "email" => "user@test.fr",
            "plainPassword" => "password",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_PROPOSAL);

        $user = new UserDto();
        $user->setId(1)->setEmail($data["email"])->setPlainPassword($data["plainPassword"])
            ->setFirstName($data["firstName"])->setLastName($data["lastName"])->setType($data["type"]);

        $this->userManager->method("findByUsername")->with($data["email"])->willReturn($user);

        /** @var UserDtoMapper $userDtoMapper */
        $userDtoMapper = $this->getService("coloc_matching.core.user_dto_mapper");
        $entity = $userDtoMapper->toEntity($user);
        $entity->addRole("ROLE_USER");

        $this->userRepository->method("find")->with($user->getId())->willReturn($entity);

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
        $request = Request::create("/rest");
        $request->headers->set("Authorization", "Bearer $token");

        $user = $this->tokenEncoder->decode($request);

        self::assertNotNull($user, "Expected to get the authenticated user");
        self::assertEquals($this->testDto->getUsername(), $user->getUsername());
    }

}