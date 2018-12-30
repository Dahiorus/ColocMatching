<?php

namespace App\Rest\Controller\v1\Message;

use App\Core\DTO\Message\PrivateMessageDto;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidRecipientException;
use App\Core\Form\Type\Message\MessageDtoForm;
use App\Core\Manager\Message\PrivateConversationDtoManagerInterface;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Security\User\TokenEncoderInterface;
use App\Rest\Controller\Response\Message\PrivateMessagePageResponse;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST controller for resources /users/{id}/messages
 *
 * @Rest\Route(path="/users/{id}/messages", requirements={ "id": "\d+" })
 * @Security(expression="is_granted('ROLE_USER')")
 *
 * @author Dahiorus
 */
class PrivateConversationController extends AbstractRestController
{
    /** @var PrivateConversationDtoManagerInterface */
    private $conversationManager;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker,
        PrivateConversationDtoManagerInterface $conversationManager, UserDtoManagerInterface $userManager,
        TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->conversationManager = $conversationManager;
        $this->userManager = $userManager;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Lists the messages between the authenticated user and a specified one
     *
     * @Rest\Get(name="rest_get_private_messages")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     *
     * @Operation(tags={ "Conversation" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Response(
     *     response=200, description="Private messages found", @Model(type=PrivateMessagePageResponse::class)),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getMessagesAction(int $id, ParamFetcher $fetcher, Request $request)
    {
        $page = $fetcher->get("page", true);
        $size = $fetcher->get("size", true);

        $this->logger->debug("Listing the messages with a user",
            array ("user id" => $id, "parameters" => array ("page" => $page, "size" => $size)));

        $pageable = new PageRequest($page, $size);
        /** @var UserDto $currentUser */
        $currentUser = $this->tokenEncoder->decode($request);
        /** @var UserDto $user */
        $user = $this->userManager->read($id);

        $response = new PageResponse(
            $this->conversationManager->listMessages($user, $currentUser, $pageable),
            "rest_get_private_messages", array ("id" => $id, "page" => $page, "size" => $size));

        $this->logger->info("Listing messages - result information", array ("response" => $response));

        return $this->buildJsonResponse($response);
    }


    /**
     * Posts a new message to a user with the authenticated user as the author of the message
     *
     * @Rest\Post(name="rest_post_private_message")
     *
     * @Operation(tags={ "Conversation" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The user identifier"),
     *   @SWG\Parameter(name="message", in="body", required=true, description="The message",
     *     @Model(type=MessageDtoForm::class)),
     *   @SWG\Response(response=201, description="Private message created", @Model(type=PrivateMessageDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=404, description="No user found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidRecipientException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function postMessageAction(int $id, Request $request)
    {
        $this->logger->debug("Posting a new message to a user",
            array ("user id" => $id, "postParams" => $request->request->all()));

        /** @var UserDto $currentUser */
        $author = $this->tokenEncoder->decode($request);
        /** @var UserDto $recipient */
        $recipient = $this->userManager->read($id);
        /** @var PrivateMessageDto $message */
        $message = $this->conversationManager->createMessage($author, $recipient, $request->request->all());

        $this->logger->info("Message posted", array ("response" => $message));

        return $this->buildJsonResponse($message, Response::HTTP_CREATED);
    }

}
