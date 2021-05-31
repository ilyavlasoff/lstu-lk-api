<?php

namespace App\Model\ExternalConsumingParam;

use JMS\Serializer\Annotation as JMS;

class NotificationReceiver
{
    /**
     * @var string | null
     * @JMS\SerializedName("id")
     * @JMS\Type("string")
     */
    private $nPersonsOid;

    /**
     * @var bool | null
     * @JMS\Type("bool")
     */
    private $mutePrivate;

    /**
     * @var bool | null
     * @JMS\Type("bool")
     */
    private $muteDiscussion;

    /**
     * @return string|null
     */
    public function getNPersonsOid(): ?string
    {
        return $this->nPersonsOid;
    }

    /**
     * @param string|null $nPersonsOid
     */
    public function setNPersonsOid(?string $nPersonsOid): void
    {
        $this->nPersonsOid = $nPersonsOid;
    }

    /**
     * @return bool|null
     */
    public function getMutePrivate(): ?bool
    {
        return $this->mutePrivate;
    }

    /**
     * @param bool|null $mutePrivate
     */
    public function setMutePrivate(?bool $mutePrivate): void
    {
        $this->mutePrivate = $mutePrivate;
    }

    /**
     * @return bool|null
     */
    public function getMuteDiscussion(): ?bool
    {
        return $this->muteDiscussion;
    }

    /**
     * @param bool|null $muteDiscussion
     */
    public function setMuteDiscussion(?bool $muteDiscussion): void
    {
        $this->muteDiscussion = $muteDiscussion;
    }


}