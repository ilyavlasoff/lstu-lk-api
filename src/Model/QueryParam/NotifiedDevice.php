<?php

namespace App\Model\QueryParam;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class NotifiedDevice
{
    /**
     * @var string | null
     * @JMS\Type("string")
     * @Assert\NotNull(message="Device FCM key not found")
     */
    private $fcmKey;

    /**
     * @return string|null
     */
    public function getFcmKey(): ?string
    {
        return $this->fcmKey;
    }

    /**
     * @param string|null $fcmKey
     */
    public function setFcmKey(?string $fcmKey): void
    {
        $this->fcmKey = $fcmKey;
    }

}