<?php

namespace App\Rest\Controller\v1\Message;

use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\Message\GroupMessageDto;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Form\Type\Message\MessageDtoForm;
use App\Core\Manager\Group\GroupDtoManagerInterface;
use App\Core\Manager\Message\GroupConversationDtoManagerInterface;
use App\Core\Repository\Filter\Pageable\PageRequest;
use App\Core\Security\User\TokenEncoderInterface;
use App\Rest\Controller\Response\Message\GroupMessagePageResponse;
use App\Rest\Controller\Response\PageResponse;
use App\Rest\Controller\v1\AbstractRestController;
use App\Rest\Security\Authorization\Voter\GroupVoter;
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
 * REST controller handling a group conversation
 *
 * @Rest\Route(path="/groups/{id}/messages", requirements={ "id": "\d+" })
 * @Security("is_granted('ROLE_USER')")
 *
 * @author Dahiorus
 */
class GroupConversationController extends AbstractRestController
{
    /** @var GroupConversationDtoManagerInterface */
    private $conversationManager;

    /** @var GroupDtoManagerInterface */
    private $groupManager;

    /** @var TokenEncoderInterface */
    private $tokenEncoder;


    public function __construct(LoggerInterface $logger, SerializerInterface $serializer,
        AuthorizationCheckerInterface $authorizationChecker, GroupConversationDtoManagerInterface $conversationManager,
        GroupDtoManagerInterface $groupManager, TokenEncoderInterface $tokenEncoder)
    {
        parent::__construct($logger, $serializer, $authorizationChecker);

        $this->conversationManager = $conversationManager;
        $this->groupManager = $groupManager;
        $this->tokenEncoder = $tokenEncoder;
    }


    /**
     * Lists a group messages
     *
     * @Rest\Get(name="rest_get_group_messages")
     * @Rest\QueryParam(name="page", nullable=true, description="The page number", requirements="\d+", default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The page size", requirements="\d+", default="20")
     *
     * @Operation(tags={ "Conversation" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Response(
     *     response=200, description="Group messages found", @Model(type=GroupMessagePageResponse::class)),
     *   @SWG\Response(response=206, description="Partial content"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Forbidden"),
     *   @SWG\Response(response=404, description="No group found")
     * )
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function getMessagesAction(int $id, ParamFetcher $fetcher)
    {
        $page = $fetcher->get("page", true);
        $size = $fetcher->get("size", true);

        $this->logger->debug("Listing a group messages",
            array ("group id" => $id, "parameters" => array ("page" => $page, "size" => $size)));

        $pageable = new PageRequest($page, $size);
        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);

        $this->evaluateUserAccess(GroupVoter::MESSAGE, $group);

        $response = new PageResponse(
            $this->conversationManager->listMessages($group, $pageable),
            "rest_get_group_messages", array ("id" => $id, "page" => $page, "size" => $size));

        $this->logger->info("Listing messages - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Posts a new message to a group with the authenticated user as the author of the message
     *
     * @Rest\Post(name="rest_post_group_message")
     *
     * @Operation(tags={ "Conversation" },
     *   @SWG\Parameter(in="path", name="id", type="integer", required=true, description="The group identifier"),
     *   @SWG\Parameter(name="message", in="body", required=true, description="The message",
     *     @Model(type=MessageDtoForm::class)),
     *   @SWG\Response(response=201, description="Message created", @Model(type=GroupMessageDto::class)),
     *   @SWG\Response(response=400, description="Bad request"),
     *   @SWG\Response(response=401, description="Unauthorized"),
     *   @SWG\Response(response=403, description="Forbidden"),
     *   @SWG\Response(response=404, description="No group found")
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws EntityNotFoundException
     * @throws InvalidParameterException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function postMessageAction(int $id, Request $request)
    {
        $this->logger->debug("Post a new message to a group",
            array ("group id" => $id, "postParams" => $request->request->all()));

        /** @var GroupDto $group */
        $group = $this->groupManager->read($id);

        $this->evaluateUserAccess(GroupVoter::MESSAGE, $group);

        /** @var UserDto $currentUser */
        $author = $this->tokenEncoder->decode($request);
        /** @var GroupMessageDto $message */
        $message = $this->conversationManager->createMessage($author, $group, $request->request->all());

        $this->logger->info("Message posted", array ("response" => $message));

        return $this->buildJsonResponse($message, Response::HTTP_CREATED);
    }

}
