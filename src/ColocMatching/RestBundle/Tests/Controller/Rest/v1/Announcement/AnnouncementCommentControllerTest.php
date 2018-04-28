<?php

namespace ColocMatching\RestBundle\Tests\Controller\Rest\v1\Announcement;

use ColocMatching\CoreBundle\DTO\Announcement\AnnouncementDto;
use ColocMatching\CoreBundle\DTO\Announcement\CommentDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageRequest;
use ColocMatching\RestBundle\Tests\AbstractControllerTest;
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
        $this->creator = $this->userManager->create(array (
            "email" => "user@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "User",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_PROPOSAL
        ));

        return $this->announcementManager->create($this->creator, array (
            "title" => "Announcement test",
            "type" => Announcement::TYPE_RENT,
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
        for ($i = 1; $i <= 8; $i++)
        {
            $author = $this->userManager->create(array (
                "email" => "author-$i@test.fr",
                "plainPassword" => "Secret1234&",
                "firstName" => "User-$i",
                "lastName" => "Test",
                "type" => UserConstants::TYPE_SEARCH
            ));
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
    public function getSomeCommentsShouldReturn206()
    {
        self::$client->request("GET", "/rest/announcements/" . $this->announcement->getId() . "/comments",
            array ("page" => 1, "size" => 3));
        self::assertStatusCode(Response::HTTP_PARTIAL_CONTENT);
    }


    /**
     * @test
     */
    public function getAllCommentsShouldReturn200()
    {
        self::$client->request("GET", "/rest/announcements/" . $this->announcement->getId() . "/comments",
            array ("page" => 1, "size" => 20));
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
    public function deleteCommentAsCreatorShouldReturn200()
    {
        /** @var CommentDto[] $comments */
        $comments = $this->announcementManager->getComments($this->announcement, new PageRequest());
        $comment = $comments[ count($comments) - 1 ];

        self::$client = self::createAuthenticatedClient($this->creator);
        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcement->getId() . "/comments/" . $comment->getId());
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteCommentAsCandidateShouldReturn200()
    {
        /** @var CommentDto[] $comments */
        $comments = $this->announcementManager->getComments($this->announcement, new PageRequest());
        $comment = $comments[0];
        /** @var UserDto $author */
        $author = $this->userManager->read($comment->getAuthorId());
        self::$client = self::createAuthenticatedClient($author);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcement->getId() . "/comments/" . $comment->getId());
        self::assertStatusCode(Response::HTTP_OK);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteCommentAsNonCandidateShouldReturn403()
    {
        /** @var CommentDto[] $comments */
        $comments = $this->announcementManager->getComments($this->announcement, new PageRequest());
        $comment = $comments[0];
        /** @var UserDto $user */
        $user = $this->userManager->create(array (
            "email" => "non-candidate@test.fr",
            "plainPassword" => "Secret1234&",
            "firstName" => "Non candidate",
            "lastName" => "Test",
            "type" => UserConstants::TYPE_SEARCH
        ));
        self::$client = self::createAuthenticatedClient($user);

        self::$client->request("DELETE",
            "/rest/announcements/" . $this->announcement->getId() . "/comments/" . $comment->getId());
        self::assertStatusCode(Response::HTTP_FORBIDDEN);
    }

}
