<?php

declare(strict_types=1);

namespace App\Entity;

class NotificationTemplate implements \JsonSerializable
{
    private Notification $notification;
    private string $name;
    private string $from = '';
    private string $to = '';
    private string $subject = '';
    /** @var mixed[] $context */
    private array $context = [];


    /**
     * @param Notification $notification
     * @param string $name
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param mixed[] $context
     */
    public function __construct(
        Notification $notification,
        string $name,
        string $from = '',
        string $to = '',
        string $subject = '',
        array $context = []
    ) {
        $this
            ->setNotification($notification)
            ->setName($name)
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setContext($context);
    }


    /**
     * @return Notification
     */
    public function getNotification(): Notification
    {
        return $this->notification;
    }


    /**
     * @param Notification $notification
     * @return $this
     */
    private function setNotification(Notification $notification): self
    {
        $this->notification = $notification;
        return $this;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @param string $name
     * @return $this
     */
    private function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }


    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }


    /**
     * @param string $from
     * @return $this
     */
    public function setFrom(string $from): self
    {
        $this->from = $from;
        return $this;
    }


    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }


    /**
     * @param string $to
     * @return $this
     */
    public function setTo(string $to): self
    {
        $this->to = $to;
        return $this;
    }


    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }


    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }


    /**
     * @return mixed[]
     */
    public function getContext(): array
    {
        return $this->context;
    }


    /**
     * @param mixed[] $context
     * @return $this
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }


    /**
     * @param mixed[] $context
     * @return $this
     */
    public function addContext(array $context): self
    {
        $this->setContext([...$this->getContext(), $context]);
        return $this;
    }


    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return [
            'from' => $this->getFrom(),
            'to' => $this->getTo(),
            'subject' => $this->getSubject(),
            'context' => $this->getContext()
        ];
    }
}
