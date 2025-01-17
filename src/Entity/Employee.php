<?php

declare(strict_types=1);

namespace App\Entity;

class Employee implements \JsonSerializable
{
    private int $userId;
    private int $employeeId;
    private string $email;
    private string $firstName;
    private string $lastName;
    private string $name;
    private Employee|null $manager = null;
    /** @var Employee[] $directReports */
    private array $directReports = [];


    /**
     * @param int $userId
     * @param int $employeeId
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param Employee|null $manager
     * @param Employee[] $directReports
     */
    public function __construct(
        int $userId,
        int $employeeId,
        string $email,
        string $firstName,
        string $lastName,
        Employee|null $manager = null,
        array $directReports = []
    ) {
        $this
            ->setUserId($userId)
            ->setEmployeeId($employeeId)
            ->setEmail($email)
            ->setName($firstName, $lastName)
            ->setManager($manager)
            ->setDirectReports($directReports);
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
     * @return int
     */
    public function getEmployeeId(): int
    {
        return $this->employeeId;
    }


    /**
     * @param int $employeeId
     * @return $this
     */
    private function setEmployeeId(int $employeeId): self
    {
        $this->employeeId = $employeeId;
        return $this;
    }


    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }


    /**
     * @param string $email
     * @return $this
     */
    private function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }


    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }


    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @param string $firstName
     * @param string $lastName
     * @return $this
     */
    private function setName(
        string $firstName,
        string $lastName
    ): self {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->name = "{$firstName} {$lastName}";
        return $this;
    }


    /**
     * @return Employee|null
     */
    public function getManager(): Employee|null
    {
        return $this->manager;
    }


    /**
     * @param Employee|null $manager
     * @return $this
     */
    public function setManager(Employee|null $manager): self
    {
        $this->manager = $manager;
        return $this;
    }


    /**
     * @return Employee[]
     */
    public function getDirectReports(): array
    {
        return $this->directReports;
    }


    /**
     * @param Employee[] $directReports
     * @param bool $append
     * @return $this
     */
    public function setDirectReports(
        array $directReports,
        bool $append = false
    ): self {
        if ($append === false) {
            $this->directReports = [];
        }
        foreach ($directReports as $directReport) {
            $this->directReports[$directReport->getUserId()] = $directReport;
        }
        return $this;
    }


    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return [
            'userId' => $this->getUserId(),
            'employeeId' => $this->getEmployeeId(),
            'email' => $this->getEmail(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName()
        ];
    }
}
