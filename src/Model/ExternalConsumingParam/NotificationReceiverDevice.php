<?php

namespace App\Model\ExternalConsumingParam;

use JMS\Serializer\Annotation as JMS;

class NotificationReceiverDevice
{
    /**
     * @var string | null
     */
    private $user;

    /**
     * @var string | null
     * @JMS\Type("string")
     */
    private $fcmKey;

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @param string|null $user
     */
    public function setUser(?string $user): void
    {
        $this->user = $user;
    }

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