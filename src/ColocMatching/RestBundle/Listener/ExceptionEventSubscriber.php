<?php

namespace ColocMatching\RestBundle\Listener;

use ColocMatching\CoreBundle\Exception\ColocMatchingException;
use FOS\RestBundle\Util\ExceptionValueMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listener catching exceptions and return a JsonResponse
 */
class ExceptionEventSubscriber implements EventSubscriberInterface {

    /**
     * @var ExceptionValueMap
     */
    private $exceptionCodeMap;


    /**
     * ExceptionEventSubscriber constructor.
     *
     * @param ExceptionValueMap $codeMap Utility to map an exception on status code
     */
    public function __construct(ExceptionValueMap $codeMap) {
        $this->exceptionCodeMap = $codeMap;
    }


    public function onKernelException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();
        $statusCode = $this->getStatusCode($exception);

        if ($exception instanceof ColocMatchingException) {
            $data = $exception->getDetails();
        }
        else {
            $data = array ("message" => $exception->getMessage(), "code" => $exception->getCode());
        }

        $response = new JsonResponse($data, $statusCode, array ("Content-Type" => "application/problem+json"), false);

        $event->setResponse($response);
    }


    public static function getSubscribedEvents() {
        return array (KernelEvents::EXCEPTION => "onKernelException");
    }


    private function getStatusCode(\Exception $exception) {
        $code = $this->exceptionCodeMap->resolveException($exception);

        if (!empty($code)) {
            return $code;
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}