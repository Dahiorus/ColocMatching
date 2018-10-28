<?php

namespace App\Core\Manager\Message;

use App\Core\DTO\Group\GroupDto;
use App\Core\DTO\Message\GroupConversationDto;
use App\Core\DTO\Message\GroupMessageDto;
use App\Core\DTO\User\UserDto;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Manager\Collection;
use App\Core\Manager\Page;
use App\Core\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\ORMException;

interface GroupConversationDtoManagerInterface
{
    /**
     * Lists a group messages
     *
     * @param GroupDto $group The group
     * @param Pageable $pageable The pagination filter
     *
     * @return Collection|Page
     * @throws ORMException
     */
    public function listMessages(GroupDto $group, Pageable $pageable = null);


    /**
     * Counts a group messages
     *
     * @param GroupDto $group The group
     *
     * @return int
     * @throws ORMException
     */
    public function countMessages(GroupDto $group) : int;


    /**
     * Posts a new message in a group conversation
     *
     * @param UserDto $author The author of the message
     * @param GroupDto $group The target group
     * @param array $data The data of the message
     * @param bool $flush If the operation must be flushed
     *
     * @return GroupMessageDto
     * @throws InvalidParameterException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function createMessage(UserDto $author, GroupDto $group, array $data, bool $flush = true) : GroupMessageDto;


    /**
     * Deletes a PrivateConversationDto
     *
     * @param GroupConversationDto $dto The entity to delete
     * @param bool $flush If the operation must be flushed
     *
     * @throws ORMException
     */
    public function delete(GroupConversationDto $dto, bool $flush = true) : void;


    /**
     * Deletes all GroupConversationDto
     */
    public function deleteAll() : void;

}
