<?php

namespace App\ArgumentResolver;

use App\Exception\ValidationException;
use App\Model\QueryParam\TeachingMaterial;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeachingMaterialValueResolver implements ArgumentValueResolverInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return TeachingMaterial::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $materialId = $request->query->get('material');

        $teachingMaterial = new TeachingMaterial();
        $teachingMaterial->setMaterial($materialId);

        $errors = $this->validator->validate($teachingMaterial);
        if(count($errors) > 0) {
            throw new ValidationException($errors);
        }

        yield $teachingMaterial;
    }
}