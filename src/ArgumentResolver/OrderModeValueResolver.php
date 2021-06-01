<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\QueryParam\OrderMode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderModeValueResolver implements ArgumentValueResolverInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return $argument->getType() === OrderMode::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $orderModeValue = $request->query->get('ord');

        $mode = new OrderMode();
        $mode->setOrderBy($orderModeValue);

        $errors = $this->validator->validate($mode);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $mode;
    }
}