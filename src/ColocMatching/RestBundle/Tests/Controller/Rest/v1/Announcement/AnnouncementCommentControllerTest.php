<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\AnnouncementNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Announcement\CommentType;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\AnnouncementMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\Announcement\CommentMock;
use ColocMatching\CoreBundle\Tests\Utils\Mock\User\UserMock;
use ColocMatching\RestBundle\Tests\Controller\Rest\v1\RestTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementCommentControllerTest extends RestTestCase {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $announcementManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var User
     */
    private $authenticatedUser;

    /**
     * @var Announcement
     */
    private $announcement;


    protected function setUp() {
        parent::setUp();

        $this->logger = $this->client->getContainer()->get("logger");

        $this->announcementManager = self::createMock(AnnouncementManager::class);
        $this->client->getKernel()->getContainer()->set("coloc_matching.core.announcement_manager",
            $this->announcementManager);

        $this->authenticatedUser = UserMock::createUser(1, "user@test.fr", "password", "User", "Test",
            UserConstants::TYPE_SEARCH);

        $this->createAnnouncementMock();
    }


    protected function tearDown() {
        $this->logger->info("End test");
    }


    private function createAnnouncementMock() {
        $id = 1;
        $user = UserMock::createUser(1, "user@test.fr", "password", "User", "Test", UserConstants::TYPE_PROPOSAL);
        $this->announcement = AnnouncementMock::createAnnouncement($id, $user, "Paris 75008", "Announcement test",
            Announcement::TYPE_SUBLEASE, 950, new \DateTime());

        $this->announcementManager->method("read")->with($id)->willReturn($this->announcement);
    }


    public function testGetCommentsActionWith200() {
        $this->logger->info("Test getting comments with status code 200");

        $comments = CommentMock::createComments(9);
        $this->announcement->setComments($comments);

        $filter = new PageableFilter();
        $filter->setSize(10)->setOrder(PageableFilter::ORDER_DESC)->setSort("createdAt");

        $this->announcementManager->expects(self::once())->method("getComments")->with($this->announcement,
            $filter)->willReturn($comments->toArray());

        $this->client->request("GET", sprintf("/rest/announcements/%d/comments", $this->announcement->getId()));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testGetCommentsActionWith206() {
        $this->logger->info("Test getting comments with status code 206");

        $comments = CommentMock::createComments(23);
        $this->announcement->setComments($comments);

        $filter = new PageableFilter();
        $filter->setSize(10)->setOrder(PageableFilter::ORDER_DESC)->setSort("createdAt")->setPage(2);

        $this->announcementManager->expects(self::once())->method("getComments")->with($this->announcement,
            $filter)->willReturn(array_slice($comments->toArray(), $filter->getOffset(), $filter->getSize()));

        $this->client->request("GET", sprintf("/rest/announcements/%d/comments", $this->announcement->getId()),
            array ("page" => 2));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_PARTIAL_CONTENT, $response["code"]);
    }


    public function testGetCommentsActionWith404() {
        $this->logger->info("Test getting comments with status code 404");

        $id = 10;
        $this->announcementManager->expects(self::once())->method("read")->with($id)
            ->willThrowException(new AnnouncementNotFoundException("id", $id));

        $this->announcementManager->expects(self::never())->method("getComments");

        $this->client->request("GET", sprintf("/rest/announcements/%d/comments", $id), array ("page" => 2));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testCreateCommentActionWith201() {
        $this->logger->info("Test creating a comment for an announcement with status code 201");

        $this->announcement->addCandidate($this->authenticatedUser);

        $data = array ("message" => "Comment test");
        $comment = CommentMock::createComment(1, $this->authenticatedUser, $data["message"]);

        $this->announcementManager->expects(self::once())->method("createComment")->with($this->announcement,
            $this->authenticatedUser, $data)->willReturn($comment);

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("POST", sprintf("/rest/announcements/%d/comments", $this->announcement->getId()), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_CREATED, $response["code"]);
    }


    public function testCreateCommentActionWith400() {
        $this->logger->info("Test creating a comment for an announcement with status code 400");

        $this->announcement->addCandidate($this->authenticatedUser);

        $data = array ("message" => "Comment test", "rate" => 50);

        $this->announcementManager->expects(self::once())->method("createComment")->with($this->announcement,
            $this->authenticatedUser, $data)->willThrowException(new InvalidFormException("Exception from test",
            $this->getForm(CommentType::class)->getErrors()));

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("POST", sprintf("/rest/announcements/%d/comments", $this->announcement->getId()), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response["code"]);
    }


    public function testCreateCommentActionWith403() {
        $this->logger->info("Test creating a comment for an announcement with status code 403");

        $data = array ("message" => "Comment test", "rate" => 50);

        $this->announcementManager->expects(self::never())->method("createComment");

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("POST", sprintf("/rest/announcements/%d/comments", $this->announcement->getId()), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_FORBIDDEN, $response["code"]);
    }


    public function testCreateCommentActionWith403OnRole() {
        $this->logger->info("Test creating a comment for an announcement with status code 403 on role");

        $data = array ("message" => "Comment test", "rate" => 50);
        $this->authenticatedUser->setType(UserConstants::TYPE_PROPOSAL);

        $this->announcementManager->expects(self::never())->method("createComment");

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("POST", sprintf("/rest/announcements/%d/comments", $this->announcement->getId()), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_FORBIDDEN, $response["code"]);
    }


    public function testCreateCommentActionWith404() {
        $this->logger->info("Test creating a comment for an announcement with status code 404");

        $data = array ("message" => "Comment test", "rate" => 50);

        $id = 10;
        $this->announcementManager->expects(self::once())->method("read")->with($id)
            ->willThrowException(new AnnouncementNotFoundException("id", $id));
        $this->announcementManager->expects(self::never())->method("createComment");

        $this->setAuthenticatedRequest($this->authenticatedUser);
        $this->client->request("POST", sprintf("/rest/announcements/%d/comments", $id), $data);
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetCommentActionWith200() {
        $this->logger->info("Test getting a comment of an announcement with 200");

        $commentId = 10;
        $this->announcement->addComment(CommentMock::createComment($commentId, $this->authenticatedUser,
            "Comment message"));

        $this->client->request("GET",
            sprintf("/rest/announcements/%d/comments/%d", $this->announcement->getId(), $commentId));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_OK, $response["code"]);
    }


    public function testGetCommentActionWith404OnAnnouncement() {
        $this->logger->info("Test getting a comment of an announcement with 404 on announcement");

        $id = 10;
        $this->announcementManager->expects(self::once())->method("read")->with($id)
            ->willThrowException(new AnnouncementNotFoundException("id", $id));

        $this->client->request("GET",
            sprintf("/rest/announcements/%d/comments/%d", $id, 12));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }


    public function testGetCommentActionWith404OnComment() {
        $this->logger->info("Test getting a comment of an announcement with 404 on comment");

        $commentId = 10;

        $this->client->request("GET",
            sprintf("/rest/announcements/%d/comments/%d", $this->announcement->getId(), $commentId));
        $response = $this->getResponseContent();

        self::assertEquals(Response::HTTP_NOT_FOUND, $response["code"]);
    }
}