<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\QueryParam\IdentifierPaginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IdentifierPaginatorValueResolver implements ArgumentValueResolverInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return $argument->getType() === IdentifierPaginator::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $paginator = new IdentifierPaginator();
        $paginator->setCount($request->query->get('c'));
        $paginator->setEdge($request->query->get('of'));

        $errors = $this->validator->validate($paginator);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $paginator;
    }
}