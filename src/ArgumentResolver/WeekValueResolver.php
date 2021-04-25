<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\Request\Week;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WeekValueResolver implements ArgumentValueResolverInterface
{
    private $serializer;

    private $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return Week::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @var \App\Model\Request\Week $week */
        $week = $this->serializer->deserialize($request->getContent(), Week::class, 'json');

        $errors = $this->validator->validate($week);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }
    }
}