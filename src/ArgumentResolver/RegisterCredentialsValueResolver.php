<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\Request\RegisterCredentials;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterCredentialsValueResolver implements ArgumentValueResolverInterface
{
    private $serializer;

    private $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return RegisterCredentials::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $registerCredentials = $this->serializer->deserialize($request->getContent(), RegisterCredentials::class, 'json');

        if (count($credentialsValidationErrors = $this->validator->validate($registerCredentials))) {
            throw new ValidationException($credentialsValidationErrors);
        }
    }
}