<?php

namespace App\Exception;

use JMS\Serializer\Annotation as JMS;
use Throwable;

abstract class AbstractSystemException extends AbstractSerializableException
{
    /**
     * @var string
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    public $type = 'system';

}