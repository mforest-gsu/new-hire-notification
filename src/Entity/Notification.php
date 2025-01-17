<?php

declare(strict_types=1);

namespace App\Entity;

class Notification
{
    private string $id;
    private int $userId;
    private NotificationTemplate $template;
    private \DateTimeInterface $timestamp;
    private string|null $statusCode = null;
    private string|null $statusDescription = null;


    /**
     * @param string|null $id
     * @param int $userId
     * @param string $template
     * @param \DateTimeInterface|null $timestamp
     * @param string|null $statusCode
     * @param string|null $statusDescription
     */
    public function __construct(
        string|null $id,
        int $userId,
        string $template,
        \DateTimeInterface|null $timestamp = null,
        string|null $statusCode = null,
        string|null $statusDescription = null
    ) {
        $this
            ->setId($id)
            ->setUserId($userId)
            ->setTemplate($template)
            ->setTimestamp($timestamp)
            ->setStatus($statusCode, $statusDescription);
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * @param string|null $id
     * @return $this
     */
    private function setId(string|null $id): self
    {
        if ($id === null) {
            $data = random_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            $id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        $this->id = $id;
        return $this;
    }


    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }


    /**
     * @param int $userId
     * @return $this
     */
    private function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }


    /**
     * @return NotificationTemplate
     */
    public function getTemplate(): NotificationTemplate
    {
        return $this->template;
    }


    /**
     * @param string $template
     * @return $this
     */
    private function setTemplate(string $template): self
    {
        $this->template = new NotificationTemplate($this, $template);
        return $this;
    }


    /**
     * @return \DateTimeInterface
     */
    public function getTimestamp(): \DateTimeInterface
    {
        return $this->timestamp;
    }


    /**
     * @param \DateTimeInterface|null $timestamp
     * @return $this
     */
    private function setTimestamp(\DateTimeInterface|null $timestamp): self
    {
        $this->timestamp = $timestamp ?? new \DateTime();
        return $this;
    }


    /**
     * @return string|null
     */
    public function getStatusCode(): string|null
    {
        return $this->statusCode;
    }


    /**
     * @return string|null
     */
    public function getStatusDescription(): string|null
    {
        return $this->statusDescription;
    }


    /**
     * @param string|null $code
     * @param string|null $description
     * @return $this
     */
    public function setStatus(string|null $code, string|null $description = null): self
    {
        $this->statusCode = $code;
        $this->statusDescription = is_string($code) ? ($description ?? $code) : null;
        return $this;
    }
}
