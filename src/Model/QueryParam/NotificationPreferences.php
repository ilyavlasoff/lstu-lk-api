<?php

namespace App\Model\QueryParam;

use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;

class NotificationPreferences
{
    /**
     * @var bool | null
     * @JMS\Type("bool")
     * @OA\Property(type="boolean", nullable=true, description="Флаг отключения уведомлений о личных сообщениях", example="true")
     */
    private $disablePrivateMessageNotifications;

    /**
     * @var bool | null
     * @JMS\Type("bool")
     * @OA\Property(type="boolean", nullable=true, description="Флаг отключения уведомлений о сообщениях в обсуждении дисциплин", example="true")
     */
    private $disableDiscussionMessageNotifications;

    /**
     * @return bool|null
     */
    public function getDisablePrivateMessageNotifications(): ?bool
    {
        return $this->disablePrivateMessageNotifications;
    }

    /**
     * @param bool|null $disablePrivateMessageNotifications
     */
    public function setDisablePrivateMessageNotifications(?bool $disablePrivateMessageNotifications): void
    {
        $this->disablePrivateMessageNotifications = $disablePrivateMessageNotifications;
    }

    /**
     * @return bool|null
     */
    public function getDisableDiscussionMessageNotifications(): ?bool
    {
        return $this->disableDiscussionMessageNotifications;
    }

    /**
     * @param bool|null $disableDiscussionMessageNotifications
     */
    public function setDisableDiscussionMessageNotifications(?bool $disableDiscussionMessageNotifications): void
    {
        $this->disableDiscussionMessageNotifications = $disableDiscussionMessageNotifications;
    }

}