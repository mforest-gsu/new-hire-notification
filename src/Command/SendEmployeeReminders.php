<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Notification;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('app:send-employee-reminders')]
class SendEmployeeReminders extends AbstractNotifyCommand
{
    /**
     * @return iterable<int,Notification>
     */
    protected function getNotifications(): iterable
    {
        foreach ($this->employeeRepository->getEmployeesToRemind() as $userId => list($employee, $daysRemaining)) {
            $assignments = $this->assignmentRepository->get($userId, false);
            if (count($assignments) > 0) {
                yield $this->createNotification(
                    $employee,
                    'EmployeeReminder',
                    'Required Training Reminder',
                    ['daysRemaining' => $daysRemaining, 'assignments' => $assignments]
                );
            }
        }
    }
}
