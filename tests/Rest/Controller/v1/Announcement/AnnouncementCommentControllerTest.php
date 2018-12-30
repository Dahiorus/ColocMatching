<?php

namespace App\Tests\Rest\Controller\v1\Announcement;

use App\Core\DTO\Announcement\AnnouncementDto;
use App\Core\DTO\Announcement\CommentDto;
use App\Core\DTO\User\UserDto;
use App\Core\Entity\Announcement\AnnouncementType;
use App\Core\Entity\User\UserStatus;
use App\Core\Manager\Announcement\AnnouncementDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Tests\Rest\AbstractControllerTest;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementCommentControllerTest extends AbstractControllerTest
{
    /** @var AnnouncementDtoManagerInterface */
    private $announcementManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var AnnouncementDto */
    private $announcement;

    /** @var UserDto */
    private $creator;


    protected function initServices() : void
    {
        $this->announcementManager = self::getService("coloc_matching.core.announcement_dto_manager");
        $this->userManager = self::getService("coloc_matching.core.user_dto_manager");
    }


    protected function initTestData() : void
    {
        $this->announcement = $this->createAnnouncement();
        $this->addComments();

        self::$client = self::initClient();
    }


    protected function clearData() : void
    {
        $this->announcementManager->deleteAll();
        $this->userManager->deleteAll();
    }


    /**
     * @return AnnouncementDto
     * @throws \Exception
     */
    private function createAnnouncement() : AnnouncementDto
    {
        $this->creator = $this->createProposalUser($this->userManager, "proposal@test.fr", UserStatus::ENABLED);

        return $this->announcementManager->create($this->creator, array (
            "title" => "Announcement test",
            "type" => AnnouncementType::RENT,
            "rentPrice" => 840,
            "startDate" => "2018-12-10",
            "location" => "rue Edouard Colonne, Paris 75001"
        ));
    }


    /**
     * @throws \Exception
     */
    private function addComments()
    {
        for ($i = 1; $i <= 4; $i++)
        {
            $author = $this->createSearchUser($this->userManager, "author-$i@test.fr", UserStatus::ENABLED);
            $this->announcementManager->addCandidate($this->announcement, $author);
            $comment = $this->announcementManager->createComment($this->announcement, $author, array (
                "message" => "Comment $i",
                "rate" => rand(0, 5)
            ));
            self::assertNotNull($comment, "Expected comment to be created");
        }
    }


    /**
     * @test
     */
    public function getCommentsShouldReturn200()
    {
        self::$client->request("GET", "/rest/announcements/" . $this->announcement->getId() . "/comments",
            array ("page" => 1, "size" => 10));
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     */
    public function getNonExistingAnnouncementCommentsShouldReturn404()
    {
        self::$client->request("GET", "/rest/announcements/0/comments",
            array ("page" => 1, "size" => 20));
        self::assertStatusCode(Response::HTTP_NOT_FOUND);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteCommentAsCreatorShouldReturn204()
    {
        /** @var CommentDto[] $comments */
        $comments = $this->announcementManager->getComments($this->announcement, new PageRequest())->getContent();
        $comment = $comments[ count($comments) - 1 ];

        self::$client = self::createAuthenticatedClient($this->creator);
        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcement->getId() . "/comments/" . $comment->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteCommentAsCandidateShouldReturn204()
    {
        /** @var CommentDto[] $comments */
        $comments = $this->announcementManager->getComments($this->announcement, new PageRequest())->getContent();
        $comment = $comments[0];
        /** @var UserDto $author */
        $author = $this->userManager->read($comment->getAuthorId());
        self::$client = self::createAuthenticatedClient($author);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcement->getId() . "/comments/" . $comment->getId());
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteCommentAsNonCandidateShouldReturn403()
    {
        /** @var CommentDto[] $comments */
        $comments = $this->announcementManager->getComments($this->announcement, new PageRequest())->getContent();
        $comment = $comments[0];
        $user = $this->createSearchUser(self::getService("coloc_matching.core.user_dto_manager"),
            "non-candidate@test.fr", UserStatus::ENABLED);
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcement->getId() . "/comments/" . $comment->getId());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }


    /**
     * @test
     */
    public function deleteNonExistingCommentShouldReturn204()
    {
        self::$client = self::createAuthenticatedClient($this->creator);
        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcement->getId() . "/comments/0");
        self::assertStatusCode(Response::HTTP_NO_CONTENT);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteCommentAsOtherCandidateShouldReturn403()
    {
        /** @var CommentDto[] $comments */
        $comments = $this->announcementManager->getComments($this->announcement, new PageRequest())->getContent();
        $comment = $comments[0];

        $candidate = $this->createSearchUser($this->userManager, "other-candidate@yopmail.com");
        $this->announcementManager->addCandidate($this->announcement, $candidate);
        self::$client = self::createAuthenticatedClient($candidate);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcement->getId() . "/comments/" . $comment->getId());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }

}
