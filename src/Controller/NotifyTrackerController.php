<?php

declare(strict_types=1);

namespace App\Controller;

class NotifyTrackerController
{
    /** @var string */
    private const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/i';


    /**
     * @param string $logDir
     * @param string $wwwDir
     */
    public function __construct(
        private string $logDir,
        private string $wwwDir
    ) {
    }


    /**
     * @param string $trackerId
     * @return void
     */
    public function process(string $trackerId): void
    {
        try {
            $this->processTrackerId(
                $trackerId,
                (new \DateTime("now", new \DateTimeZone("America/New_York")))
            );
        } catch (\Throwable) {
        }

        header("Content-Type: image/png");
        header("Cache-Control: max-age=0, no-cache, no-store, private");
        echo file_get_contents($this->wwwDir . '/pixel.png');
    }


    /**
     * @param string $trackerId
     * @param \DateTimeInterface $timestamp
     * @return void
     */
    private function processTrackerId(
        string $trackerId,
        \DateTimeInterface $timestamp
    ): void {
        if (preg_match(self::UUID_REGEX, $trackerId) !== 1) {
            throw new \RuntimeException('preg_match(self::UUID_REGEX, $trackerId) !== 1');
        }

        $fp = fopen($this->logDir . '/tracker.log', "a");
        if (!is_resource($fp)) {
            throw new \RuntimeException('!is_resource($fp)');
        }

        try {
            if (!flock($fp, LOCK_EX)) {
                throw new \RuntimeException('!flock($fp, LOCK_EX)');
            }

            fputcsv($fp, [$trackerId, $timestamp->format('Y-m-d H:i:s')]);

            fflush($fp);

            flock($fp, LOCK_UN);
        } finally {
            fclose($fp);
        }
    }
}
