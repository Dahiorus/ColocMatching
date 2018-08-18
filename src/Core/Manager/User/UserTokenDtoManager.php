<?php

namespace App\Core\Manager\User;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\User\UserTokenDto;
use App\Core\Entity\User\UserToken;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Mapper\User\UserTokenDtoMapper;
use App\Core\Repository\User\UserTokenRepository;
use App\Core\Service\UserTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class UserTokenDtoManager implements UserTokenDtoManagerInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var EntityManagerInterface */
    private $em;

    /** @var UserTokenRepository */
    private $repository;

    /** @var UserTokenDtoMapper */
    private $dtoMapper;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, UserTokenDtoMapper $dtoMapper)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->repository = $em->getRepository(UserToken::class);
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

        if (!empty($this->repository->findOneBy(array ("username" => $user->getUsername(), "reason" => $reason))))
        {
            throw new InvalidParameterException("username",
                "A user token already exists with the reason '$reason' for the username " . $user->getUsername());
        }

        $tokenGenerator = new UserTokenGenerator();
        /** @var UserToken $userToken */
        $userToken = $tokenGenerator->generateToken($user->getUsername(), $reason);

        $this->em->persist($userToken);
        $this->flush($flush);

        $this->logger->info("User token created", array ("token" => $userToken));

        return $this->dtoMapper->toDto($userToken);
    }


    /**
     * @inheritdoc
     */
    public function findByToken(string $token, string $reason = null)
    {
        $this->logger->debug("Finding a user token", array ("value" => $token, "reason" => $reason));

        $criteria = array ("token" => $token);

        if (!empty($reason))
        {
            $criteria["reason"] = $reason;
        }

        /** @var UserToken $userToken */
        $userToken = $this->repository->findOneBy($criteria);

        if (empty($userToken))
        {
            throw new EntityNotFoundException($this->getDomainClass(), "token", $token);
        }

        $this->logger->info("User token found", array ("user token" => $userToken));

        return $this->dtoMapper->toDto($userToken);
    }


    /**
     * @inheritdoc
     */
    public function delete(UserTokenDto $userToken, bool $flush = true) : void
    {
        $entity = $this->repository->find($userToken->getId());

        $this->logger->debug("Deleting a user token",
            array ("domainClass" => $this->getDomainClass(), "id" => $userToken->getId(), "flush" => $flush));

        $this->em->remove($entity);
        $this->flush($flush);

        $this->logger->debug("User token deleted",
            array ("domainClass" => $this->getDomainClass(), "id" => $userToken->getId()));
    }


    /**
     * @inheritdoc
     */
    public function deleteAll() : void
    {
        $this->logger->debug("Deleting all entities", array ("domainClass" => $this->getDomainClass()));

        $this->repository->deleteAll();
        $this->flush(true);

        $this->logger->info("All entities deleted", array ("domainClass" => $this->getDomainClass()));
    }


    /**
     * Calls the entity manager to flush the operations and clears all managed objects
     *
     * @param bool $flush If the operations must be flushed
     */
    protected function flush(bool $flush) : void
    {
        if ($flush)
        {
            $this->em->flush();
            $this->em->clear();
        }
    }


    protected function getDomainClass() : string
    {
        return UserToken::class;
    }

}
