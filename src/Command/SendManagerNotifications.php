<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Notification;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('app:send-manager-notifications')]
class SendManagerNotifications extends AbstractNotifyCommand
{
    /**
     * @return iterable<int,Notification>
     */
    protected function getNotifications(): iterable
    {
        foreach ($this->employeeRepository->getManagersToRemind() as $manager) {
            $directReports = $manager->getDirectReports();
            if (count($directReports) > 0) {
                yield $this->createNotification(
                    $manager,
                    'ManagerNotification',
                    'Required Training Notification',
                    ['directReports' => $directReports]
                );
            }
        }
    }
}
