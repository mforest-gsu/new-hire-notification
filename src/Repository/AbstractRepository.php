<?php

declare(strict_types=1);

namespace App\Repository;

use Oracle\OCI\Connection;

abstract class AbstractRepository
{
    /**
     * @param mixed $v
     * @return int|null
     */
    public static function toIntNull(mixed $v): int|null
    {
        return is_numeric($v) ? intval($v) : null;
    }


    private string $sqlDir;
    private int|null $orgUnitId = null;


    /**
     * @param Connection $oci
     * @param string|null $sqlDir
     */
    public function __construct(
        protected Connection $oci,
        string|null $sqlDir = null
    ) {
        $this->sqlDir = $sqlDir ?? dirname(__DIR__, 2) . '/sql/Query';
    }


    /**
     * @param string $sql
     * @return resource
     */
    protected function sql(string $sql): mixed
    {
        $path = $this->sqlDir . '/' . $sql;
        $file = fopen($path, 'r');

        return is_resource($file)
            ? $file
            : throw new \RuntimeException("File not found: " . $path);
    }


    /**
     * @return int
     */
    protected function getOrgUnitId(): int
    {
        $this->orgUnitId ??= self::toIntNull(
            $this->oci
                ->parse($this->sql('GetOrgUnit.sql'))
                ->execute()
                ->fetch()['OrgUnitId'] ?? null
        );
        return $this->orgUnitId ?? throw new \RuntimeException();
    }
}
