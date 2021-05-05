<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\Request\UserIdentifier;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserIdentifierValueResolver implements ArgumentValueResolverInterface
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
        return UserIdentifier::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $userIdentifier = $this->serializer->deserialize($request->getContent(), UserIdentifier::class, 'json');

        if (count($validationErrors = $this->validator->validate($userIdentifier)) > 0) {
            throw new ValidationException($validationErrors, 'UserIdentifier');
        }

        yield $userIdentifier;

        // TODO: Check user already registered
    }
}