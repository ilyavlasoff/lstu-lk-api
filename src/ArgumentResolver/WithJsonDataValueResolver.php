<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\Request\WithJsonFlag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WithJsonDataValueResolver implements ArgumentValueResolverInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return WithJsonFlag::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $withDataFlag = $request->query->get('withjsondata');
        $withJsonData = new WithJsonFlag();
        $withJsonData->setWithJsonData((bool)$withDataFlag);

        if(count($errors = $this->validator->validate($withJsonData)) > 0) {
            throw new ValidationException($errors);
        }

        yield $withJsonData;
    }
}