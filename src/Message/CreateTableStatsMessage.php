<?php

namespace StatisticsBundle\Message;

use Tourze\AsyncContracts\AsyncMessageInterface;

class CreateTableStatsMessage implements AsyncMessageInterface
{
    private string $startTime;

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function setStartTime(string $startTime): void
    {
        $this->startTime = $startTime;
    }

    private string $endTime;

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function setEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }

    private string $statsTable;

    public function getStatsTable(): string
    {
        return $this->statsTable;
    }

    public function setStatsTable(string $statsTable): void
    {
        $this->statsTable = $statsTable;
    }

    /** @var array<string, array{0: string, 1: string, 2: string}> */
    private array $statColumns;

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public function getStatColumns(): array
    {
        return $this->statColumns;
    }

    /**
     * @param array<string, array{0: string, 1: string, 2: string}> $statColumns
     */
    public function setStatColumns(array $statColumns): void
    {
        $this->statColumns = $statColumns;
    }

    private string $tableName;

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }
}
