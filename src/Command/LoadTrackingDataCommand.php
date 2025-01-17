<?php

declare(strict_types=1);

namespace App\Command;

use Oracle\OCI\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:load-tracking-data')]
class LoadTrackingDataCommand extends Command
{
    /**
     * @param Connection $oci
     */
    public function __construct(private Connection $oci)
    {
        parent::__construct();
    }


    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument(
            'logPath',
            InputArgument::REQUIRED,
            'Path to tracker log'
        );
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
        list($logPath, $tmpPath) = $this->getPaths($input);
        try {
            if (!file_exists($logPath)) {
                throw new \RuntimeException('!file_exists($logPath)');
            }
            if (!(filesize($logPath) > 1)) {
                return self::SUCCESS;
            }

            $tmpFile = $this->openFile($tmpPath, "a+");
            try {
                $this
                    ->copyFile($logPath, $tmpFile)
                    ->loadFile($tmpFile);
            } finally {
                fclose($tmpFile);
            }
        } catch (\Throwable $t) {
            throw new \RuntimeException(
                sprintf(
                    "execute() failed; \$logPath = '%s'; \$tmpPath = '%s';",
                    $logPath,
                    $tmpPath
                ),
                0,
                $t
            );
        }

        return self::SUCCESS;
    }


    /**
     * @param InputInterface $input
     * @return array{string,string}
     */
    private function getPaths(InputInterface $input): array
    {
        /** @var string $logPath */
        $logPath = $input->getArgument('logPath');
        return [
            $logPath,
            sprintf("%s/%s.%s", dirname($logPath), basename($logPath), date("Ymd_His"))
        ];
    }


    /**
     * @param mixed $path
     * @param string $mode
     * @return resource
     */
    private function openFile(
        mixed $path,
        string $mode
    ): mixed {
        if (!is_string($path)) {
            throw new \RuntimeException('!is_string($path)');
        }

        $file = fopen($path, $mode);
        if (!is_resource($file)) {
            throw new \RuntimeException('!is_resource($file)');
        }

        if (!flock($file, LOCK_EX)) {
            fclose($file);
            throw new \RuntimeException('!flock($file, LOCK_EX)');
        }

        return $file;
    }


    /**
     * @param string $logPath
     * @param resource $tmpFile
     * @return $this
     */
    private function copyFile(
        string $logPath,
        mixed $tmpFile
    ): self {
        $logFile = $this->openFile($logPath, "r+");

        try {
            if (stream_copy_to_stream($logFile, $tmpFile) === false) {
                throw new \RuntimeException('stream_copy_to_stream($logFile, $tmpFile) === false');
            }

            ftruncate($logFile, 0);
            fflush($logFile);

            if (rewind($tmpFile) === false) {
                throw new \RuntimeException('rewind($tmpFile) === false');
            }
        } finally {
            fclose($logFile);
        }

        return $this;
    }


    /**
     * @param resource $tmpFile
     * @return self
     */
    private function loadFile(mixed $tmpFile): self
    {
        $stmt = $this->oci->parse("
          INSERT INTO HRE_NOTIFY_LOG
            (
              HRE_NOTIFY_LOG_SEQNUM,
              HRE_NOTIFY_LOG_ID,
              HRE_NOTIFY_LOG_TIMESTAMP
            )
          VALUES
            (
              HRE_NOTIFY_LOG_SEQ.NEXTVAL,
              :HRE_NOTIFY_LOG_ID,
              TO_DATE(:HRE_NOTIFY_LOG_TIMESTAMP, 'yyyy-mm-dd hh24:mi:ss')
            )
        ");

        try {
            for ($row = fgetcsv($tmpFile, 64); is_array($row); $row = fgetcsv($tmpFile, 64)) {
                /** @var array{string,string} $row */
                list ($notificationId, $timestamp) = $row;
                $stmt
                    ->bindByName(':HRE_NOTIFY_LOG_ID', $notificationId)
                    ->bindByName(':HRE_NOTIFY_LOG_TIMESTAMP', $timestamp)
                    ->execute(OCI_NO_AUTO_COMMIT);
            }

            $this->oci->commit();
        } catch (\Throwable $t) {
            $this->oci->rollback();
            throw $t;
        }

        $this->oci
            ->parse("
              DELETE FROM
                HRE_NOTIFY_LOG o
              WHERE
                o.HRE_NOTIFY_LOG_SEQNUM > (
                  SELECT
                    MIN(i.HRE_NOTIFY_LOG_SEQNUM)
                  FROM
                    HRE_NOTIFY_LOG i
                  where
                    i.HRE_NOTIFY_LOG_ID = o.HRE_NOTIFY_LOG_ID AND
                    i.HRE_NOTIFY_LOG_TIMESTAMP = o.HRE_NOTIFY_LOG_TIMESTAMP
                )
            ")
            ->execute();

        return $this;
    }
}
