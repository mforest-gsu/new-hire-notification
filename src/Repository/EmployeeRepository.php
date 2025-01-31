<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Employee;

/**
 * @phpstan-type HRE_EMPLOYEE array{
 *   HRE_EMPLOYEE_USER_ID:string,
 *   HRE_EMPLOYEE_ID:string,
 *   HRE_EMPLOYEE_EMAIL_ADDRESS:string,
 *   HRE_EMPLOYEE_FIRST_NAME:string,
 *   HRE_EMPLOYEE_LAST_NAME:string,
 *   HRE_MANAGER_USER_ID?:string,
 *   HRE_MANAGER_ID?:string,
 *   HRE_MANAGER_EMAIL_ADDRESS?:string,
 *   HRE_MANAGER_FIRST_NAME?:string,
 *   HRE_MANAGER_LAST_NAME?:string,
 * }
 */
class EmployeeRepository extends AbstractRepository
{
    /**
     * @return iterable<int,Employee>
     */
    public function getEmployeesToNotify(): iterable
    {
        foreach ($this->query('GetNotifyEmployee.sql') as $userId => list($employee,)) {
            yield $userId => $employee;
        }
    }


    /**
     * @return iterable<int,array{Employee,int}>
     */
    public function getEmployeesToRemind(): iterable
    {
        foreach ($this->query('GetRemindEmployee.sql') as $userId => list($employee,$row)) {
            /** @var array{HRE_EMPLOYEE_FIRST_NOTIFY?:string|null} $row */
            $firstNotify = is_string($row['HRE_EMPLOYEE_FIRST_NOTIFY'] ?? null)
                ? new \DateTime($row['HRE_EMPLOYEE_FIRST_NOTIFY'])
                : null;
            $daysRemaining = $firstNotify?->diff(new \DateTime())?->days;
            if (is_int($daysRemaining)) {
                yield $userId => [$employee, max($daysRemaining, 0)];
            }
        }
    }


    /**
     * @return iterable<int,Employee>
     */
    public function getManagersToRemind(): iterable
    {
        /** @var Employee|null $currentManager */
        $currentManager = null;

        foreach ($this->query('GetNotifyManager.sql') as list($manager,)) {
            $currentManager ??= $manager;
            $currentManager->setDirectReports($manager->getDirectReports());
            if ($currentManager->getUserId() !== $manager->getUserId()) {
                yield $currentManager->getUserId() => $currentManager;
                $currentManager = $manager;
            }
        }

        if ($currentManager !== null) {
            yield $currentManager->getUserId() => $currentManager;
        }
    }


    /**
     * @param string $sql
     * @return iterable<int,array{Employee,mixed[]}>
     */
    private function query(string $sql): iterable
    {
        $orgUnitId = $this->getOrgUnitId();

        /** @var iterable<int,HRE_EMPLOYEE> $result */
        $result = $this->oci
            ->parse($this->sql($sql))
            ->bindByName(':OrgUnitId', $orgUnitId)
            ->query();

        foreach ($result as $row) {
            $employee = new Employee(
                intval($row['HRE_EMPLOYEE_USER_ID']),
                intval($row['HRE_EMPLOYEE_ID']),
                $row['HRE_EMPLOYEE_EMAIL_ADDRESS'],
                $row['HRE_EMPLOYEE_FIRST_NAME'],
                $row['HRE_EMPLOYEE_LAST_NAME']
            );

            if (isset($row['HRE_MANAGER_USER_ID'])) {
                $employee = new Employee(
                    intval($row['HRE_MANAGER_USER_ID']),
                    intval($row['HRE_MANAGER_ID'] ?? 0),
                    $row['HRE_MANAGER_EMAIL_ADDRESS'] ?? '',
                    $row['HRE_MANAGER_FIRST_NAME'] ?? '',
                    $row['HRE_MANAGER_LAST_NAME'] ?? '',
                    null,
                    [$employee]
                );
            }

            yield $employee->getUserId() => [$employee, $row];
        }
    }
}
