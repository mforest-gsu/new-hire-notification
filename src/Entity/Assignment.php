<?php

declare(strict_types=1);

namespace App\Entity;

class Assignment implements \JsonSerializable
{
    private int $id;
    private string $name;
    private bool $completed;


    /**
     * @param int $id
     * @param string $name
     * @param bool|int $completed
     */
    public function __construct(
        int $id,
        string $name,
        bool|int $completed
    ) {
        $this
            ->setId($id)
            ->setName($name)
            ->setCompleted($completed);
    }


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;
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
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }


    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }


    /**
     * @param bool|int $completed
     * @return $this
     */
    public function setCompleted(bool|int $completed): self
    {
        $this->completed = intval($completed) > 0;
        return $this;
    }


    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'completed' => $this->isCompleted()
        ];
    }
}
