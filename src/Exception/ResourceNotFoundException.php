<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;
use JMS\Serializer\Annotation as JMS;

class ResourceNotFoundException extends AbstractUserException
{
    protected $httpCode = Response::HTTP_NOT_FOUND;

    /**
     * @var string
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    public $resourceName;

    /**
     * @JMS\Expose()
     */
    public $resourceIdentifier;

    public function __construct($resourceName, $resourceIdentifier, Throwable $previous = null)
    {
        $this->resourceName = $resourceName;
        $this->resourceIdentifier = $resourceIdentifier;

        $message = 'Unable to find resource';
        $userMessage = 'Запрашиваемый Вами ресурс не найден';

        parent::__construct('ERR_NO_RESOURCE', $message, $userMessage, 0, $previous);
    }
}