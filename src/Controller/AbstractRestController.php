<?php

namespace App\Controller;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractRestController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    protected function responseSuccessWithObject(
        $response,
        $status = Response::HTTP_OK,
        SerializationContext $context = null
    ): JsonResponse {
        return new JsonResponse($this->serializer->serialize($response, 'json', $context), $status, [], true);
    }

    protected function responseSuccess(): JsonResponse
    {
        return new JsonResponse(json_encode(['success' => true]), Response::HTTP_OK, [], true);
    }

}