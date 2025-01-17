<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Assignment;
use App\Entity\Employee;
use App\Entity\Notification;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('app:test')]
class TestCommand extends AbstractNotifyCommand
{
    /**
     * @return iterable<int,Notification>
     */
    protected function getNotifications(): iterable
    {
        $employee = new Employee(
            1,
            1,
            'mforest@gsu.edu',
            'Melody',
            'Kimball',
            null,
            [
                new Employee(2, 2, 'jbarger@gsu.edu', 'Jeb', 'Barger'),
                new Employee(3, 3, 'jfloyd23@gsu.edu', 'Eric', 'Floyd'),
                new Employee(4, 4, 'rdean11@gsu.edu', 'Randy', 'Dean'),
            ]
        );

        yield $this->createNotification(
            $employee,
            'EmployeeNotification',
            'Required Training Notification'
        );

        yield $this->createNotification(
            $employee,
            'EmployeeReminder',
            'Required Training Reminder',
            [
                'daysRemaining' => 25,
                'assignments' => [
                    new Assignment(1, 'Right to Know Acknowledgment', 0),
                    new Assignment(2, 'USG Ethics Acknowledgment', 0),
                    new Assignment(3, 'Safety Acknowledgment', 0),
                    new Assignment(4, 'Preventing Harassment & Discrimination in the Workplace Acknowledgment', 0),
                ],
                'loginLink' => 'https://gastate.view.usg.edu/d2l/login'
            ]
        );

        yield $this->createNotification(
            $employee,
            'ManagerNotification',
            'Required Training Notification',
            [
                'directReports' => array_values($employee->getDirectReports())
            ]
        );
    }
}
