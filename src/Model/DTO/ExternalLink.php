<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;

class ExternalLink
{
    /**
     * @var string | null
     * @JMS\Type("string")
     * @OA\Property(type="string", nullable=true, description="Текст ссылки", example="Пояснительная записка ВКР")
     */
    private $linkText;

    /**
     * @var string | null
     * @JMS\Type("string")
     * @OA\Property(type="string", nullable=true, description="Значение ссылки", example="https://disk.yandex.ru/d/dfFer3-sdFewa")
     */
    private $linkContent;

    /**
     * @return string|null
     */
    public function getLinkText(): ?string
    {
        return $this->linkText;
    }

    /**
     * @param string|null $linkText
     */
    public function setLinkText(?string $linkText): void
    {
        $this->linkText = $linkText;
    }

    /**
     * @return string|null
     */
    public function getLinkContent(): ?string
    {
        return $this->linkContent;
    }

    /**
     * @param string|null $linkContent
     */
    public function setLinkContent(?string $linkContent): void
    {
        $this->linkContent = $linkContent;
    }

}