<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Employee;
use App\Entity\Notification;
use App\Repository\AssignmentRepository;
use App\Repository\EmployeeRepository;
use App\Repository\NotificationRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;

abstract class AbstractNotifyCommand extends Command
{
    /**
     * @param EmployeeRepository $employeeRepository
     * @param AssignmentRepository $assignmentRepository
     * @param NotificationRepository $notificationRepository
     * @param MailerInterface $mailer
     * @param string $fromEmail
     */
    public function __construct(
        protected EmployeeRepository $employeeRepository,
        protected AssignmentRepository $assignmentRepository,
        private NotificationRepository $notificationRepository,
        private MailerInterface $mailer,
        private string $fromEmail = "GSU Onboarding <requiredtraining@gsu.edu>"
    ) {
        parent::__construct();
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        foreach ($this->getNotifications() as $notification) {
            try {
                $this->sendNotification($notification);
            } catch (\Throwable $t) {
                $notification->setStatus("Error", json_encode([
                    'type' => get_class($t),
                    'mesg' => $t->getMessage()
                ], JSON_THROW_ON_ERROR));

                $output->writeln(["sendNotification() failed", $t->__toString()]);
            }

            $this->logNotification($notification, $output);

            try {
                $this->saveNotification($notification);
            } catch (\Throwable $t) {
                $output->writeln($t->__toString());
                $output->writeln(["saveNotification() failed", $t->__toString()]);
            }
        }

        return self::SUCCESS;
    }


    /**
     * @return iterable<int,Notification>
     */
    abstract protected function getNotifications(): iterable;


    /**
     * @param Employee $employee
     * @param string $template
     * @param string $subject
     * @param mixed[] $context
     * @return Notification
     */
    protected function createNotification(
        Employee $employee,
        string $template,
        string $subject,
        array $context = []
    ): Notification {
        return $this->notificationRepository->create(
            $employee->getUserId(),
            $template,
            $this->fromEmail,
            "{$employee->getName()} <{$employee->getEmail()}>",
            $subject,
            $context
        );
    }


    /**
     * @param Notification $notification
     * @return void
     */
    protected function sendNotification(Notification $notification): void
    {
        $email = $this->createEmail($notification);
        $this->mailer->send($email);
        $notification->setStatus("Success");
    }


    /**
     * @param Notification $notification
     * @return TemplatedEmail
     */
    protected function createEmail(Notification $notification): TemplatedEmail
    {
        $template = $notification->getTemplate();
        return (new TemplatedEmail())
            ->from($template->getFrom())
            ->to($template->getTo())
            ->subject($template->getSubject())
            ->htmlTemplate($template->getName() . '.html.twig')
            ->context([
                ...$notification->getTemplate()->getContext(),
                'notification' => $notification
            ])
            ->locale('en');
    }


    /**
     * @param Notification $notification
     * @return void
     */
    protected function saveNotification(Notification $notification): void
    {
        $this->notificationRepository->save($notification);
    }


    /**
     * @param Notification $notification
     * @param OutputInterface $output
     * @return void
     */
    protected function logNotification(
        Notification $notification,
        OutputInterface $output
    ): void {
        $output->writeln(implode(',', array_map(json_encode(...), [
            $notification->getId(),
            $notification->getUserId(),
            $notification->getTemplate()->getName(),
            $notification->getTemplate(),
            $notification->getStatusCode(),
            $notification->getStatusDescription()
        ])));
    }
}
