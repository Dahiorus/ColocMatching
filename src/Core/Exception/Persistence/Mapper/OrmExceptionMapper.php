<?php

namespace App\Core\Exception\Persistence\Mapper;

use App\Core\Exception\Persistence\NonUniqueResultException;
use App\Core\Exception\Persistence\NoResultException;
use App\Core\Exception\Persistence\PersistenceException;
use App\Core\Exception\Persistence\TransactionRequiredException;
use App\Core\Exception\Persistence\UnexpectedResultException;
use Doctrine\ORM\ORMException;

/**
 * Exception mapper to convert ORM exception to internal exception
 */
class OrmExceptionMapper
{
    public static function convert(ORMException $exception) : PersistenceException
    {
        if ($exception instanceof \Doctrine\ORM\NonUniqueResultException)
        {
            return new NonUniqueResultException($exception->getCode());
        }

        if ($exception instanceof \Doctrine\ORM\NoResultException)
        {
            return new NoResultException($exception->getCode());
        }

        if ($exception instanceof \Doctrine\ORM\TransactionRequiredException)
        {
            return new TransactionRequiredException($exception->getCode());
        }

        if ($exception instanceof \Doctrine\ORM\UnexpectedResultException)
        {
            return new UnexpectedResultException($exception->getCode());
        }

        return new PersistenceException("Unexpected persistence exception", $exception->getCode(), $exception);
    }
}