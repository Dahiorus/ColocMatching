<?php

namespace ColocMatching\CoreBundle\Manager\User;

use ColocMatching\CoreBundle\DAO\UserTokenDao;
use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\DTO\User\UserTokenDto;
use ColocMatching\CoreBundle\Entity\User\UserToken;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Mapper\User\UserTokenDtoMapper;
use ColocMatching\CoreBundle\Service\UserTokenGenerator;
use Psr\Log\LoggerInterface;

class UserTokenDtoManager implements UserTokenDtoManagerInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var UserTokenDao */
    private $dao;

    /** @var UserTokenDtoMapper */
    private $dtoMapper;


    public function __construct(LoggerInterface $logger, UserTokenDao $dao, UserTokenDtoMapper $dtoMapper)
    {
        $this->logger = $logger;
        $this->dao = $dao;
        $this->dtoMapper = $dtoMapper;
    }


    /**
     * @inheritdoc
     */
    public function create(UserDto $user, string $reason, bool $flush = true) : UserTokenDto
    {
        $this->logger->debug("Creating a user token", array ("user" => $user, "reason" => $reason, "flush" => $flush));

        if (!in_array($reason, array (UserToken::REGISTRATION_CONFIRMATION, UserToken::LOST_PASSWORD)))
        {
            throw new InvalidParameterException("reason");
        }

        if (!empty($this->dao->findOne(array ("username" => $user->getUsername(), "reason" => $reason))))
        {
            throw new InvalidParameterException("username",
                "A user token already exists with the reason '$reason' for the username " . $user->getUsername());
        }

        $tokenGenerator = new UserTokenGenerator();
        /** @var UserToken $userToken */
        $userToken = $this->dao->persist(
            $tokenGenerator->generateToken($user->getUsername(), $reason));
        $this->flush($flush);

        $this->logger->debug("User token created", array ("token" => $userToken));

        return $this->dtoMapper->toDto($userToken);
    }


    /**
     * @inheritdoc
     */
    public function findByToken(string $token)
    {
        $this->logger->debug("Finding a user token", array ("value" => $token));

        /** @var UserToken $userToken */
        $userToken = $this->dao->findOne(array ("token" => $token));

        if (empty($userToken))
        {
            throw new EntityNotFoundException($this->getDomainClass(), "token", $token);
        }

        $this->logger->debug("User token found", array ("user token" => $userToken));

        return $this->dtoMapper->toDto($userToken);
    }


    /**
     * @inheritdoc
     */
    public function delete(UserTokenDto $userToken, bool $flush = true) : void
    {
        $entity = $this->dao->get($userToken->getId());

        $this->logger->debug("Deleting a user token",
            array ("domainClass" => $this->getDomainClass(), "id" => $userToken->getId(), "flush" => $flush));

        $this->dao->delete($entity);
        $this->flush($flush);

        $this->logger->debug("User token deleted",
            array ("domainClass" => $this->getDomainClass(), "id" => $userToken->getId()));
    }


    protected function flush(bool $flush) : void
    {
        if ($flush)
        {
            $this->dao->flush();
        }
    }


    protected function getDomainClass() : string
    {
        return UserToken::class;
    }

}