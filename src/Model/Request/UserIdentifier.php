<?php

namespace App\Model\Request;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserIdentifier
{
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="Username field can not be blank")
     * @Assert\NotNull(message="Username value was not found")
     */
    private $username;

    /**
     * @JMS\Type("string")
     * @Assert\NotNull(message="Document value was not found")
     * @Assert\NotBlank(message="Document field can not be blank")
     * @Assert\Regex(pattern="/^\d+$/", message="Document number can only contain digits")
     */
    private $zBookNumber;

    /**
     * @JMS\Type("int")
     * @Assert\NotNull(message="Enter year field was not found")
     * @Assert\NotBlank(message="Enter year field can not be blank")
     */
    private $enteredYear;

    /**
     * @param ExecutionContextInterface $context
     * @param $payload
     * @throws \Exception
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        $currentDate = new \DateTime();
        $currentYear = $currentDate->format('Y');
        if ($this->enteredYear > $currentYear) {
            $context->buildViolation("Enter year seems too late, maximal value $currentYear")
                ->atPath('enteredYear')
                ->addViolation();
        }
        elseif ($this->enteredYear < $currentYear - 10) {
            $context->buildViolation("Enter year is too early, minimal value is " . (string)($currentYear - 10))
                ->atPath('enteredYear')
                ->addViolation();
        }
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getZBookNumber()
    {
        return $this->zBookNumber;
    }

    /**
     * @param mixed $zBookNumber
     */
    public function setZBookNumber($zBookNumber): void
    {
        $this->zBookNumber = $zBookNumber;
    }

    /**
     * @return mixed
     */
    public function getEnteredYear()
    {
        return $this->enteredYear;
    }

    /**
     * @param mixed $enteredYear
     */
    public function setEnteredYear($enteredYear): void
    {
        $this->enteredYear = $enteredYear;
    }


}