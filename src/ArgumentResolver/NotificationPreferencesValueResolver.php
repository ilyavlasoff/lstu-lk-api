<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\QueryParam\NotificationPreferences;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NotificationPreferencesValueResolver implements ArgumentValueResolverInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return $argument->getType() === NotificationPreferences::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        /** @var NotificationPreferences $preferences */
        $preferences = $this->serializer->deserialize($request->getContent(), NotificationPreferences::class, 'json');

        $errors = $this->validator->validate($preferences);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $preferences;
    }
}