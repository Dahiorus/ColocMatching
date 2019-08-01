<?php

namespace App\Rest\Listener;

use App\Core\Exception\ColocMatchingException;
use App\Core\Exception\Persistence\Mapper\OrmExceptionMapper;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Util\ExceptionValueMap;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listener catching exceptions and return a JsonResponse
 *
 * @author Dahiorus
 */
class ExceptionEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ExceptionValueMap
     */
    private $exceptionCodeMap;


    /**
     * ExceptionEventSubscriber constructor.
     *
     * @param LoggerInterface $logger
     * @param ExceptionValueMap $codeMap Utility to map an exception on status code
     */
    public function __construct(LoggerInterface $logger, ExceptionValueMap $codeMap)
    {
        $this->logger = $logger;
        $this->exceptionCodeMap = $codeMap;
    }


    public static function getSubscribedEvents()
    {
        return array (KernelEvents::EXCEPTION => "onKernelException");
    }


    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getException();
        $statusCode = $this->getStatusCode($exception);

        if ($exception instanceof ColocMatchingException)
        {
            $data = $exception->getDetails();
        }
        else if ($exception instanceof ORMException)
        {
            $data = OrmExceptionMapper::convert($exception)->getDetails();
        }
        else
        {
            return;
        }

        $this->logger->error($exception->getMessage(), array ("exception" => $exception));

        $event->setResponse(
            new JsonResponse($data, $statusCode, array ("Content-Type" => "application/problem+json"), false));
    }


    private function getStatusCode(\Exception $exception)
    {
        $code = $this->exceptionCodeMap->resolveException($exception);

        if (!empty($code))
        {
            return $code;
        }

        if ($exception instanceof HttpExceptionInterface)
        {
            return $exception->getStatusCode();
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

}
