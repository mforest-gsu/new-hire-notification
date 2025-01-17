<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Assignment;

class AssignmentRepository extends AbstractRepository
{
    /**
     * @param int $userId
     * @param bool|null $completed
     * @return Assignment[]
     */
    public function get(
        int $userId,
        bool|null $completed = null
    ): array {
        $assignments = [];

        $orgUnitId = $this->getOrgUnitId();

        /** @var iterable<int,array{Id:string,Name:string,Completed:int}> $result */
        $result = $this->oci
            ->parse($this->sql('GetAssignments.sql'))
            ->bindByName(':OrgUnitId', $orgUnitId)
            ->bindByName(':UserId', $userId)
            ->query();

        foreach ($result as $row) {
            $assignment = new Assignment(
                intval($row['Id']),
                $row['Name'],
                $row['Completed']
            );

            if ($completed === null || $completed === $assignment->isCompleted()) {
                $assignments[$assignment->getId()] = $assignment;
            }
        }

        return $assignments;
    }
}
