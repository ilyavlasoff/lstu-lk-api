<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\QueryParam\NotifiedDevice;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NotifiedDeviceValueResolver implements ArgumentValueResolverInterface
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
        return $argument->getType() === NotifiedDevice::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @var NotifiedDevice $device */
        $device = $this->serializer->deserialize($request->getContent(), NotifiedDevice::class, 'json');

        $errors = $this->validator->validate($device);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $device;
    }
}