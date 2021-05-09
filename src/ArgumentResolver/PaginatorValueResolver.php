<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\QueryParam\Paginator;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaginatorValueResolver implements ArgumentValueResolverInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return Paginator::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $paginator = new Paginator();
        $paginator->setOffset($request->query->get('of'));
        $paginator->setCount($request->query->get('c'));

        $errors = $this->validator->validate($paginator);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $paginator;
    }
}