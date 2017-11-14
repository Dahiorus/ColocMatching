<?php

namespace ColocMatching\CoreBundle\Exception\Persistence\Mapper;

use ColocMatching\CoreBundle\Exception\Persistence\NonUniqueResultException;
use ColocMatching\CoreBundle\Exception\Persistence\NoResultException;
use ColocMatching\CoreBundle\Exception\Persistence\PersistenceException;
use ColocMatching\CoreBundle\Exception\Persistence\TransactionRequiredException;
use ColocMatching\CoreBundle\Exception\Persistence\UnexpectedResultException;
use Doctrine\ORM\ORMException;

/**
 * Exception mapper to convert ORM exception to internal exception
 */
class OrmExceptionMapper {

    public static function convert(ORMException $exception) : PersistenceException {
        if ($exception instanceof \Doctrine\ORM\NonUniqueResultException) {
            return new NonUniqueResultException($exception->getCode());
        }

        if ($exception instanceof \Doctrine\ORM\NoResultException) {
            return new NoResultException($exception->getCode());
        }

        if ($exception instanceof \Doctrine\ORM\TransactionRequiredException) {
            return new TransactionRequiredException($exception->getCode());
        }

        if ($exception instanceof \Doctrine\ORM\UnexpectedResultException) {
            return new UnexpectedResultException();
        }

        return new PersistenceException();
    }
}