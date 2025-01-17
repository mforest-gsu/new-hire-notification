<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Notification;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('app:send-employee-notifications')]
class SendEmployeeNotifications extends AbstractNotifyCommand
{
    /**
     * @return iterable<int,Notification>
     */
    protected function getNotifications(): iterable
    {
        foreach ($this->employeeRepository->getEmployeesToNotify() as $employee) {
            yield $this->createNotification(
                $employee,
                'EmployeeNotification',
                'Required Training Notification'
            );
        }
    }
}
