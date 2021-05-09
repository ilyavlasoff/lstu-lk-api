<?php

namespace App\ArgumentResolver;

use App\Exception\DataAccessException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Model\QueryParam\PictureSize;
use App\Repository\PersonalRepository;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PictureSizeValueResolver implements ArgumentValueResolverInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return PictureSize::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $userPicRequest = new PictureSize();
        $userPicRequest->setImageSize($request->query->get('size'));

        $errors = $this->validator->validate($userPicRequest);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $userPicRequest;
    }
}