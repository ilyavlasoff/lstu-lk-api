<?php

namespace App\EventListener;

use App\Exception\ISystemException;
use App\Exception\IUserException;
use App\Model\Response\Exception\SystemExceptionWarning;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    private $serializer;

    public function __construct(SerializerInterface $serializer) {
        $this->serializer = $serializer;
    }

    public function onExceptionJsonResponse(ExceptionEvent $exceptionEvent) {
        $exception = $exceptionEvent->getThrowable();

        $jsonResponse = new JsonResponse();

        if ($exception instanceof ISystemException) {
            $errorData = $exception->toSystemExceptionWarning();
        }
        elseif ($exception instanceof IUserException) {
            $errorData = $exception->toUserExceptionWarning();
        } else {
            $errorData = new SystemExceptionWarning();
            if($exception instanceof HttpExceptionInterface) {
                $jsonResponse->setStatusCode($exception->getStatusCode());
                $jsonResponse->headers->replace($exception->getHeaders());
            } else {
                $jsonResponse->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                $errorData->setError('ERR_INTERNAL');
                $errorData->setMessage('Internal server error');
            }

        }

        $jsonResponse->setContent($this->serializer->serialize($errorData, 'json'));
    }
}