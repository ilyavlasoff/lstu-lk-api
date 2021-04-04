<?php

namespace App\Controller;

use App\Exception\ISystemException;
use App\Exception\IUserException;
use App\Model\Response\Exception\SystemExceptionWarning;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractRestController extends AbstractController
{
    protected $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function responseSuccessWithObject($responseData, int $responseStatus = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($responseData, 'json'), $responseStatus, [], true);
    }

    public function responseWithError(\Exception $e, int $responseStatus): JsonResponse
    {
        if ($e instanceof ISystemException) {
            $errorWarningResponse = $e->toSystemExceptionWarning();
        }
        elseif ($e instanceof IUserException) {
            $errorWarningResponse = $e->toUserExceptionWarning();
        } else {
            $errorWarningResponse = new SystemExceptionWarning();
            $errorWarningResponse->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $errorWarningResponse->setError('ERR_INTERNAL_SERVER_ERROR');
            $errorWarningResponse->setMessage('Undefined server error');
        }

        return new JsonResponse(
            $this->serializer->serialize($errorWarningResponse, 'json'), $errorWarningResponse->getCode(),
            [],
            true
        );
    }
}