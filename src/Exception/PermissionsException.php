<?php

namespace App\Exception;

use App\Document\User;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PermissionsException extends AbstractSystemException
{
    protected $httpCode = Response::HTTP_FORBIDDEN;

    /**
     * @var string
     * Resource name
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    public $resource;

    /**
     * @JMS\Expose()
     * Unavailable resource identifier
     */
    public $identifier;

    /**
     * @var string
     * @JMS\Expose()
     * @JMS\Type("string")
     * Current user
     */
    public $user;

    public function __construct($resource, $identifier, User $currentUser = null, Throwable $previous = null)
    {
        $this->resource = $resource;
        $this->identifier = $identifier;

        if($currentUser) {
            $this->user = $currentUser->getEmail();
        } else {
            $this->user = 'Unauthorized user';
        }
        $message = 'Can not access resource due to permissions restrictions';

        parent::__construct('ERR_PERMISSIONS', $message, 0, $previous);
    }
}