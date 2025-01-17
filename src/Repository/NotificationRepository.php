<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Notification;

class NotificationRepository extends AbstractRepository
{
    /**
     * @param int $userId
     * @param string $template
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param mixed[] $context
     * @return Notification
     */
    public function create(
        int $userId,
        string $template,
        string $from,
        string $to,
        string $subject,
        array $context = []
    ): Notification {
        return (new Notification(null, $userId, $template))
            ->getTemplate()
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->setContext($context)
            ->getNotification();
    }


    /**
     * @param string $id
     * @return Notification|null
     */
    public function get(string $id): Notification|null
    {
        /**
         * @var array{
         *   HRE_NOTIFY_ID:string,
         *   HRE_NOTIFY_USER_ID:string,
         *   HRE_NOTIFY_TEMPLATE:string,
         *   HRE_NOTIFY_TIMESTAMP:string,
         *   HRE_NOTIFY_CONTEXT:string,
         *   HRE_NOTIFY_STATUS_CODE:string,
         *   HRE_NOTIFY_STATUS_DESCRIPTION:string
         * }|null $row
         */
        $row = $this->oci
            ->parse($this->sql('GetNotification.sql'))
            ->bindByName(':HRE_NOTIFY_ID', $id)
            ->execute()
            ->fetch();
        if ($row === null) {
            return null;
        }

        $notification = new Notification(
            $row['HRE_NOTIFY_ID'],
            intval($row['HRE_NOTIFY_USER_ID']),
            $row['HRE_NOTIFY_TEMPLATE'],
            new \DateTime($row['HRE_NOTIFY_TIMESTAMP']),
            $row['HRE_NOTIFY_STATUS_CODE'],
            $row['HRE_NOTIFY_STATUS_DESCRIPTION']
        );

        /** @var array{from:string,to:string,subject:string,context:mixed[]} $context */
        $context = json_decode($row['HRE_NOTIFY_CONTEXT'], true, 512, JSON_THROW_ON_ERROR);
        return $notification
            ->getTemplate()
            ->setFrom($context['from'])
            ->setTo($context['to'])
            ->setSubject($context['subject'])
            ->setContext($context['context'])
            ->getNotification();
    }


    /**
     * @param Notification $notification
     * @return void
     */
    public function save(Notification $notification): void
    {
        $op = $this->get($notification->getId()) === null ? 'Create' : 'Update';

        $id = $notification->getId();
        $userId = $notification->getUserId();
        $template = $notification->getTemplate()->getName();
        $timestamp = $notification->getTimestamp()->format('Y-m-d H:i:s');
        $context = json_encode($notification->getTemplate());
        $statusCode = $notification->getStatusCode();
        $statusDesc = $notification->getStatusDescription();

        $this->oci
            ->parse($this->sql("{$op}Notification.sql"))
            ->bindByName(':HRE_NOTIFY_ID', $id)
            ->bindByName(':HRE_NOTIFY_USER_ID', $userId)
            ->bindByName(':HRE_NOTIFY_TEMPLATE', $template)
            ->bindByName(':HRE_NOTIFY_TIMESTAMP', $timestamp)
            ->bindByName(':HRE_NOTIFY_CONTEXT', $context)
            ->bindByName(':HRE_NOTIFY_STATUS_CODE', $statusCode)
            ->bindByName(':HRE_NOTIFY_STATUS_DESC', $statusDesc)
            ->execute()
            ->getConnection()
            ->commit();
    }
}
