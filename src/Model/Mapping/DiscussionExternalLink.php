<?php

namespace App\Model\Mapping;

class DiscussionExternalLink
{
    /**
     * @var string | null
     */
    private $linkText;

    /**
     * @var string | null
     */
    private $linkLocation;

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
    public function getLinkLocation(): ?string
    {
        return $this->linkLocation;
    }

    /**
     * @param string|null $linkLocation
     */
    public function setLinkLocation(?string $linkLocation): void
    {
        $this->linkLocation = $linkLocation;
    }

}