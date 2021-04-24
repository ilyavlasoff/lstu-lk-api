<?php

namespace App\Service\Validation;

use App\Exception\ParameterExistenceException;
use App\Exception\ParameterTypeException;
use App\Exception\ParameterValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractValidationService
{
    private $validator;

    public $existenceConstraints = [];
    public $typeConstraints = [];
    public $valueConstraints = [];

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($value, $valueParameterName) {
        $this->validateExistenceConstraints($value, $valueParameterName);
        $this->validateTypeConstraints($value, $valueParameterName);
        $this->validateValueConstraints($value, $valueParameterName);
    }

    protected function validateExistenceConstraints($value, $name) {
        $errors = $this->validator->validate($value, $this->existenceConstraints);
        if(count($errors) > 0) {
            throw new ParameterExistenceException($errors[0], $name);
        }
    }

    protected function validateTypeConstraints($value, $name) {
        $errors = $this->validator->validate($value, $this->typeConstraints);
        if(count($errors) > 0) {
            throw new ParameterTypeException($name, gettype($value), $errors[0]);
        }
    }

    protected function validateValueConstraints($value, $name) {
        $errors = $this->validator->validate($value, $this->valueConstraints);
        if(count($errors) > 0) {
            throw new ParameterValueException($name, $value, $errors);
        }
    }
}