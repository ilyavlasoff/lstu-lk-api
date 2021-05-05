<?php

namespace App\EventListener;

use App\Exception\RestException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelInterface;

class ExceptionListener
{
    private $serializer;
    private $environment;

    public function __construct(SerializerInterface $serializer, KernelInterface $kernel) {
        $this->serializer = $serializer;
        $this->environment = $kernel->getEnvironment();
    }

    public function onKernelException(ExceptionEvent $exceptionEvent) {
        $exception = $exceptionEvent->getThrowable();

        if(!$exception instanceof RestException) {
            if($this->environment === 'dev') {
                $exception = new RestException('DEBUG_ERROR', $exception->getMessage());
            } else {
                $exception = new RestException('ERR_INTERNAL', 'Internal server error');
            }
        }

        $jsonResponse = new JsonResponse();
        $errorData = $this->serializer->serialize(
            $exception, 'json', (new SerializationContext())->setGroups('rest-response'));
        $jsonResponse->setStatusCode($exception->getStatusCode());
        $jsonResponse->setContent($errorData);
        $exceptionEvent->setResponse($jsonResponse);
    }
}