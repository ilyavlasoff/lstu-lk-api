<?php

namespace App\Model\Mapping;

class BinaryFile
{
    /**
     * @var string | null
     */
    private $filename;

    /**
     * @var string | null
     */
    private $fileContent;

    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     */
    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return string|null
     */
    public function getFileContent(): ?string
    {
        return $this->fileContent;
    }

    /**
     * @param string|null $fileContent
     */
    public function setFileContent(?string $fileContent): void
    {
        $this->fileContent = $fileContent;
    }

}