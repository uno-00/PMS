<?php

namespace App\Support;

use PDO;
use RuntimeException;

/**
 * Exports a SQLite database file to a MySQL-compatible .sql dump.
 */
final class SqliteToMysqlExporter
{
    public function __construct(
        private readonly string $sqlitePath,
        private readonly string $databaseName = 'pms_procurement',
    ) {}

    public function export(): string
    {
        if (! is_file($this->sqlitePath)) {
            throw new RuntimeException("SQLite database not found: {$this->sqlitePath}");
        }

        $pdo = new PDO('sqlite:'.$this->sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $lines = [
            '-- PMS Procurement Management System',
            '-- MySQL database export',
            '-- Generated: '.now()->toDateTimeString(),
            '-- Source: '.basename($this->sqlitePath),
            '',
            'SET NAMES utf8mb4;',
            'SET FOREIGN_KEY_CHECKS=0;',
            'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
            'SET time_zone = "+00:00";',
            '',
            "CREATE DATABASE IF NOT EXISTS `{$this->databaseName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
            "USE `{$this->databaseName}`;",
            '',
        ];

        $tables = $pdo->query("
            SELECT name
            FROM sqlite_master
            WHERE type = 'table'
              AND name NOT LIKE 'sqlite_%'
            ORDER BY name
        ")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $create = $pdo->query("
                SELECT sql
                FROM sqlite_master
                WHERE type = 'table' AND name = ".$pdo->quote($table)
            )->fetchColumn();

            if (! $create) {
                continue;
            }

            $lines[] = "DROP TABLE IF EXISTS `{$table}`;";
            $lines[] = $this->convertCreateTable((string) $create).';';
            $lines[] = '';

            $columns = $pdo->query("PRAGMA table_info({$table})")->fetchAll();
            $columnNames = array_map(fn (array $col) => $col['name'], $columns);

            if ($columnNames === []) {
                continue;
            }

            $quotedColumns = implode(', ', array_map(fn (string $c) => "`{$c}`", $columnNames));
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll();

            foreach ($rows as $row) {
                $values = [];

                foreach ($columnNames as $column) {
                    $values[] = $this->quoteValue($row[$column] ?? null);
                }

                $lines[] = "INSERT INTO `{$table}` ({$quotedColumns}) VALUES (".implode(', ', $values).');';
            }

            if ($rows !== []) {
                $lines[] = '';
            }
        }

        $indexes = $pdo->query("
            SELECT sql
            FROM sqlite_master
            WHERE type = 'index'
              AND sql IS NOT NULL
              AND name NOT LIKE 'sqlite_%'
            ORDER BY name
        ")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($indexes as $indexSql) {
            $lines[] = $this->convertIndex((string) $indexSql).';';
        }

        $lines[] = '';
        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function convertCreateTable(string $sql): string
    {
        $sql = preg_replace('/\s+/', ' ', trim($sql)) ?? $sql;
        $sql = preg_replace('/^CREATE TABLE IF NOT EXISTS /i', 'CREATE TABLE ', $sql) ?? $sql;
        $sql = str_replace('"', '`', $sql);
        $sql = preg_replace('/`([^`]+)`\s+varchar(?!\()/i', '`$1` varchar(255)', $sql) ?? $sql;
        $sql = preg_replace('/`id`\s+integer\s+primary\s+key\s+autoincrement\s+not\s+null/i', '`id` bigint unsigned not null auto_increment primary key', $sql) ?? $sql;
        $sql = preg_replace('/`id`\s+integer\s+primary\s+key\s+autoincrement/i', '`id` bigint unsigned not null auto_increment primary key', $sql) ?? $sql;
        $sql = preg_replace("/default\s+'(\([^)]+\))'/i", 'default $1', $sql) ?? $sql;
        $sql = preg_replace('/,\s*primary key\s*\(`id`\)/i', '', $sql) ?? $sql;
        $sql = preg_replace('/,\s*foreign key/i', ', foreign key', $sql) ?? $sql;

        if (! str_contains(strtolower($sql), 'engine=')) {
            $sql = preg_replace('/\)\s*$/', ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci', $sql) ?? $sql;
        }

        return $sql;
    }

    private function convertIndex(string $sql): string
    {
        $sql = preg_replace('/\s+/', ' ', trim($sql)) ?? $sql;
        $sql = str_replace('"', '`', $sql);
        $sql = preg_replace('/\bon `/i', ' ON `', $sql) ?? $sql;

        return $sql;
    }

    private function quoteValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return "'".str_replace(["\\", "'"], ["\\\\", "''"], (string) $value)."'";
    }
}
