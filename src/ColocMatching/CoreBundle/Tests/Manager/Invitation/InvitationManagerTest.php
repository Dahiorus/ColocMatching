<?php

namespace ColocMatching\CoreBundle\Tests\Manager\Invitation;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\Group\Group;
use ColocMatching\CoreBundle\Entity\Invitation\AnnouncementInvitation;
use ColocMatching\CoreBundle\Entity\Invitation\GroupInvitation;
use ColocMatching\CoreBundle\Entity\Invitation\Invitation;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Exception\InvitationNotFoundException;
use ColocMatching\CoreBundle\Form\Type\Invitation\InvitationType;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationManager;
use ColocMatching\CoreBundle\Manager\Invitation\InvitationManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\InvitationFilter;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Invitation\InvitationRepository;
use ColocMatching\CoreBundle\Tests\TestCase;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Group\GroupMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Invitation\InvitationMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\CoreBundle\Validator\EntityValidator;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

abstract class InvitationManagerTest extends TestCase {

    /**
     * @var InvitationManagerInterface
     */
    protected $invitationManager;

    /**
     * @var string
     */
    protected $invitableClass;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityValidator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Invitation
     */
    protected $mockInvitation;


    protected function setUp() {
        $invitationClass = $this->getInvitationClass($this->invitableClass);
        $this->repository = $this->createMock(InvitationRepository::class);
        $this->entityValidator = $this->createMock(EntityValidator::class);
        $this->objectManager = $this->createMock(EntityManager::class);
        $this->objectManager->expects(self::once())->method("getRepository")->with($invitationClass)
            ->willReturn($this->repository);
        $this->logger = self::getContainer()->get("logger");

        $this->invitationManager = new InvitationManager($this->objectManager, $invitationClass, $this->entityValidator,
            $this->logger);

        $this->mockInvitation();
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    private function mockInvitation() {
        $invitable = $this->createInvitable();
        $recipient = UserMock::createUser(99, "recipient@test.fr", "password", "Recipient", "Test",
            UserConstants::TYPE_SEARCH);
        $sourceType = Invitation::SOURCE_SEARCH;

        $this->mockInvitation = InvitationMock::createInvitation(1, $invitable, $recipient, $sourceType);
    }


    private function getInvitationClass(string $invitableClass) {
        $invitationClass = null;

        switch ($invitableClass) {
            case Announcement::class:
                $invitationClass = AnnouncementInvitation::class;
                break;
            case Group::class:
                $invitationClass = GroupInvitation::class;
                break;
            default:
                throw new \Exception("Unknown invitable class");
        }

        return $invitationClass;
    }


    private function createInvitable() {
        $invitable = null;

        switch ($this->invitableClass) {
            case Announcement::class:
                $creator = UserMock::createUser(1, "proposal@test.fr", "password", "User", "Test",
                    UserConstants::TYPE_PROPOSAL);
                $invitable = AnnouncementMock::createAnnouncement(1, $creator,
                    "Paris 75010", "Announcement test", Announcement::TYPE_RENT, 1000,
                    new \DateTime());
                break;
            case Group::class:
                $creator = UserMock::createUser(1, "search@test.fr", "password", "User", "Test",
                    UserConstants::TYPE_SEARCH);
                $invitable = GroupMock::createGroup(1, $creator, "Group test", "Group from test");
                break;
            default:
                throw new \Exception("Unknown invitable class");
        }

        return $invitable;
    }


    public function testList() {
        $this->logger->info("Test listing invitations", array ("invitableClass" => $this->invitableClass));

        $filter = new PageableFilter();
        $expectedInvitations = InvitationMock::createInvitationPage($filter, 50, $this->invitableClass);

        $this->repository->expects(self::once())->method("findByPageable")->with($filter)->willReturn($expectedInvitations);

        $invitations = $this->invitationManager->list($filter);

        self::assertNotNull($invitations);
        self::assertEquals($expectedInvitations, $invitations);
    }


    public function testListByInvitable() {
        $this->logger->info("Test listing invitations by invitable", array ("invitableClass" => $this->invitableClass));

        $filter = new PageableFilter();
        $invitable = $this->createInvitable();
        $expectedInvitations = InvitationMock::createInvitationPageForInvitable($filter, 50, $invitable);

        $this->repository->expects(self::once())->method("findByInvitable")->with($invitable,
            $filter)->willReturn($expectedInvitations);

        $invitations = $this->invitationManager->listByInvitable($invitable, $filter);

        self::assertNotNull($invitations);
        self::assertEquals($expectedInvitations, $invitations);
    }


    public function testListByRecipient() {
        $this->logger->info("Test listing invitations by recipient", array ("invitableClass" => $this->invitableClass));

        $filter = new PageableFilter();
        $recipient = UserMock::createUser(1, "recipient@test.fr", "password", "Recipient", "Test",
            UserConstants::TYPE_SEARCH);
        $expectedInvitations = InvitationMock::createInvitationPage($filter, 50, $this->invitableClass, $recipient);

        $this->repository->expects(self::once())->method("findByRecipient")->with($recipient,
            $filter)->willReturn($expectedInvitations);

        $invitations = $this->invitationManager->listByRecipient($recipient, $filter);

        self::assertNotNull($invitations);
        self::assertEquals($expectedInvitations, $invitations);
    }


    public function testCreateWithSuccess() {
        $this->logger->info("Test creating an invitation with success",
            array ("invitableClass" => $this->invitableClass));

        $invitable = $this->createInvitable();
        $recipient = UserMock::createUser(99, "recipient@test.fr", "password", "Recipient", "Test",
            UserConstants::TYPE_SEARCH);
        $sourceType = Invitation::SOURCE_SEARCH;
        $expectedInvitation = InvitationMock::createInvitation(1, $invitable, $recipient, $sourceType);

        $this->entityValidator->expects(self::once())->method("validateEntityForm")->with(Invitation::create($invitable,
            $recipient, $sourceType), array (), InvitationType::class, true)->willReturn($expectedInvitation);
        $this->objectManager->expects(self::once())->method("persist")->with($expectedInvitation);

        $invitation = $this->invitationManager->create($invitable, $recipient, $sourceType, array ());

        self::assertNotNull($invitation);
        self::assertEquals($expectedInvitation, $invitation);
    }


    public function testCreateWithFailure() {
        $this->logger->info("Test creating an invitation with failure",
            array ("invitableClass" => $this->invitableClass));

        $invitable = $this->createInvitable();
        $recipient = UserMock::createUser(99, "recipient@test.fr", "password", "Recipient", "Test",
            UserConstants::TYPE_SEARCH);
        $sourceType = Invitation::SOURCE_SEARCH;

        $this->entityValidator->expects(self::once())->method("validateEntityForm")->with(Invitation::create($invitable,
            $recipient, $sourceType), array (), InvitationType::class,
            true)->willThrowException(new InvalidFormDataException("Exception from test",
            self::getForm(InvitationType::class)->getErrors()));
        $this->objectManager->expects(self::never())->method("persist");
        $this->expectException(InvalidFormDataException::class);

        $this->invitationManager->create($invitable, $recipient, $sourceType, array ());
    }


    public function testReadWithSuccess() {
        $this->logger->info("Test getting an invitation with success",
            array ("invitableClass" => $this->invitableClass));

        $this->repository->expects(self::once())->method("findById")->with($this->mockInvitation->getId())->willReturn($this->mockInvitation);

        $invitation = $this->invitationManager->read(1);

        self::assertNotNull($invitation);
        self::assertEquals($this->mockInvitation, $invitation);
    }


    public function testReadWithNotFound() {
        $this->logger->info("Test getting an non existing invitation",
            array ("invitableClass" => $this->invitableClass));

        $this->repository->expects(self::once())->method("findById")->with(1)->willReturn(null);
        $this->expectException(InvitationNotFoundException::class);

        $this->invitationManager->read(1);
    }


    public function testDelete() {
        $this->logger->info("Test deleting an invitation", array ("invitableClass" => $this->invitableClass));

        $this->objectManager->expects(self::once())->method("remove")->with($this->mockInvitation);

        $this->invitationManager->delete($this->mockInvitation);
    }


    public function testAnswerWithAccept() {
        $this->logger->info("Test accepting an invitation", array ("invitableClass" => $this->invitableClass));

        $expected = InvitationMock::createInvitation($this->mockInvitation->getId(),
            $this->mockInvitation->getInvitable(), $this->mockInvitation->getRecipient(),
            $this->mockInvitation->getSourceType());
        $expected->setStatus(Invitation::STATUS_ACCEPTED);
        $invitable = $expected->getInvitable();
        $invitable->addInvitee($this->mockInvitation->getRecipient());

        // $this->objectManager->expects(self::once())->method("persist")->with($invitable);
        $this->objectManager->expects(self::once())->method("persist")->with($expected);

        $this->invitationManager->answer($this->mockInvitation, true);
    }


    public function testAnswerWithRefuse() {
        $this->logger->info("Test refusing an invitation", array ("invitableClass" => $this->invitableClass));

        $expected = InvitationMock::createInvitation($this->mockInvitation->getId(),
            $this->mockInvitation->getInvitable(), $this->mockInvitation->getRecipient(),
            $this->mockInvitation->getSourceType());
        $expected->setStatus(Invitation::STATUS_REFUSED);

        $this->objectManager->expects(self::once())->method("persist")->with($expected);

        $this->invitationManager->answer($this->mockInvitation, false);
    }


    public function testAnswerWithFailure() {
        $this->logger->info("Test answering an invitation with failure",
            array ("invitableClass" => $this->invitableClass));

        $this->mockInvitation->setStatus(Invitation::STATUS_ACCEPTED);

        $this->objectManager->expects(self::never())->method("persist");
        $this->expectException(UnprocessableEntityHttpException::class);

        $this->invitationManager->answer($this->mockInvitation, true);
    }


    public function testSearch() {
        $this->logger->info("Test searching invitations", array ("invitableClass" => $this->invitableClass));

        $filter = new InvitationFilter();
        $expectedInvitations = InvitationMock::createInvitationPage($filter, 35, $this->invitableClass);

        $this->repository->expects(self::once())->method("findByFilter")->with($filter)->willReturn($expectedInvitations);

        $invitations = $this->invitationManager->search($filter);

        self::assertNotNull($invitations);
        self::assertEquals($expectedInvitations, $invitations);
    }

}
