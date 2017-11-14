<?php

namespace ColocMatching\RestBundle\Controller\Rest\v1\Message;

use ColocMatching\CoreBundle\Entity\User\PrivateMessage;
use ColocMatching\CoreBundle\Entity\User\User;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidRecipientException;
use ColocMatching\CoreBundle\Exception\UserNotFoundException;
use ColocMatching\CoreBundle\Manager\Message\PrivateConversationManagerInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\RestBundle\Controller\Rest\RestController;
use ColocMatching\RestBundle\Controller\Rest\Swagger\Message\PrivateConversationControllerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST controller for resources /users/{id}/messages
 *
 * @Rest\Route("/users/{id}/messages", requirements={ "id": "\d+" })
 * @Security(expression="has_role('ROLE_USER')")
 *
 * @author Dahiorus
 */
class PrivateConversationController extends RestController implements PrivateConversationControllerInterface {

    /**
     * Lists the messages between the authenticated user and the one specified by its identifier
     *
     * @Rest\Get(path="", name="rest_get_private_messages")
     * @Rest\QueryParam(name="page", nullable=true, description="The page of the paginated search", requirements="\d+",
     *   default="1")
     * @Rest\QueryParam(name="size", nullable=true, description="The number of results to return", requirements="\d+",
     *   default="20")
     *
     * @param int $id
     * @param ParamFetcher $fetcher
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     */
    public function getMessagesAction(int $id, ParamFetcher $fetcher) {
        $page = $fetcher->get("page", true);
        $size = $fetcher->get("size", true);

        $this->get("logger")->info("Listing the messages with a user",
            array ("user id" => $id, "filter" => array ("page" => $page, "size" => $size)));

        /** @var PrivateConversationManagerInterface $manager */
        $manager = $this->get("coloc_matching.core.private_conversation_manager");
        /** @var PageableFilter $filter */
        $filter = $this->get("coloc_matching.core.filter_factory")->createPageableFilter($page, $size,
            PageableFilter::ORDER_ASC, "createdAt");
        /** @var User $currentUser */
        $currentUser = $this->extractUser();
        /** @var User $user */
        $user = $this->get("coloc_matching.core.user_manager")->read($id);
        /** @var array<PrivateMessage> $messages */
        $messages = $manager->listMessages($user, $currentUser, $filter);

        $response = $this->get("coloc_matching.rest.response_factory")->createPageResponse($messages,
            $manager->countMessages($user, $currentUser), $filter);

        $this->get("logger")->debug("Listing messages - result information", array ("response" => $response));

        return $this->buildJsonResponse($response,
            ($response->hasNext()) ? Response::HTTP_PARTIAL_CONTENT : Response::HTTP_OK);
    }


    /**
     * Posts a new message to a user with the authenticated user as the author of the message
     *
     * @Rest\Post(path="", name="rest_post_private_message")
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws UserNotFoundException
     * @throws InvalidRecipientException
     * @throws InvalidFormException
     */
    public function postMessageAction(int $id, Request $request) {
        $this->get("logger")->info("Posting a new message to a user",
            array ("user id" => $id, "request" => $request->request));

        /** @var PrivateConversationManagerInterface $manager */
        $manager = $this->get("coloc_matching.core.private_conversation_manager");
        /** @var User $author */
        $author = $this->extractUser($request);
        /** @var User $recipient */
        $recipient = $this->get("coloc_matching.core.user_manager")->read($id);
        /** @var PrivateMessage $message */
        $message = $manager->createMessage($author, $recipient, $request->request->all());

        $this->get("logger")->info("Message posted", array ("response" => $message));

        return $this->buildJsonResponse($message, Response::HTTP_CREATED);
    }

}
