<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\Request\TextQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TextQueryValueResolver implements ArgumentValueResolverInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return TextQuery::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $query = new TextQuery();
        $query->setQueryString($request->query->get('q'));

        $errors = $this->validator->validate($query);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $query;
    }
}