<?php

namespace ColocMatching\CoreBundle\Manager\Message;

use ColocMatching\CoreBundle\DTO\Group\GroupDto;
use ColocMatching\CoreBundle\DTO\Message\GroupConversationDto;
use ColocMatching\CoreBundle\DTO\Message\GroupMessageDto;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;
use Doctrine\ORM\ORMException;

interface GroupConversationDtoManagerInterface
{
    /**
     * Lists a group messages
     *
     * @param GroupDto $group The group
     * @param Pageable $pageable The pagination filter
     *
     * @return GroupMessageDto[]
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function listMessages(GroupDto $group, Pageable $pageable = null) : array;


    /**
     * Counts a group messages
     *
     * @param GroupDto $group The group
     *
     * @return int
     * @throws EntityNotFoundException
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
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function createMessage(UserDto $author, GroupDto $group, array $data, bool $flush = true) : GroupMessageDto;


    /**
     * Deletes a PrivateConversationDto
     *
     * @param GroupConversationDto $dto The entity to delete
     * @param bool $flush If the operation must be flushed
     *
     * @throws EntityNotFoundException
     * @throws ORMException
     */
    public function delete(GroupConversationDto $dto, bool $flush = true) : void;


    /**
     * Deletes all GroupConversationDto
     */
    public function deleteAll() : void;

}
