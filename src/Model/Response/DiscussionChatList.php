<?php

namespace App\Model\Response;

use App\Model\Mapping\DiscussionMessage;

class DiscussionChatList
{
    /**
     * @var string
     */
    private $education;

    /**
     * @var string
     */
    private $semester;

    /**
     * @var string
     */
    private $discipline;

    /**
     * @var string
     */
    private $offset;

    /**
     * @var string
     */
    private $nextOffset;

    /**
     * @var string
     */
    private $remains;

    /**
     * @var DiscussionMessage[]
     */
    private $messages;

    /**
     * @return string
     */
    public function getEducation(): string
    {
        return $this->education;
    }

    /**
     * @param string $education
     */
    public function setEducation(string $education): void
    {
        $this->education = $education;
    }

    /**
     * @return string
     */
    public function getSemester(): string
    {
        return $this->semester;
    }

    /**
     * @param string $semester
     */
    public function setSemester(string $semester): void
    {
        $this->semester = $semester;
    }

    /**
     * @return string
     */
    public function getDiscipline(): string
    {
        return $this->discipline;
    }

    /**
     * @param string $discipline
     */
    public function setDiscipline(string $discipline): void
    {
        $this->discipline = $discipline;
    }

    /**
     * @return string
     */
    public function getOffset(): string
    {
        return $this->offset;
    }

    /**
     * @param string $offset
     */
    public function setOffset(string $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return string
     */
    public function getNextOffset(): string
    {
        return $this->nextOffset;
    }

    /**
     * @param string $nextOffset
     */
    public function setNextOffset(string $nextOffset): void
    {
        $this->nextOffset = $nextOffset;
    }

    /**
     * @return string
     */
    public function getRemains(): string
    {
        return $this->remains;
    }

    /**
     * @param string $remains
     */
    public function setRemains(string $remains): void
    {
        $this->remains = $remains;
    }

    /**
     * @return DiscussionMessage[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param DiscussionMessage[] $messages
     */
    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

}