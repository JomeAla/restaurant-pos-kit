<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'pos:db-backup {--filename=}';
    protected $description = 'Backup the database';

    public function handle(): int
    {
        $connection = config('database.default');
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = $this->option('filename') ?? 'backup-' . now()->format('Y-m-d-His') . '.sql';

        if ($connection === 'mysql') {
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $destPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s > "%s" 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                $destPath
            );

            exec($command, $output, $exitCode);

            if ($exitCode === 0 && file_exists($destPath)) {
                $this->info("Backup created: {$filename} (" . number_format(filesize($destPath) / 1024, 1) . ' KB)');
                return Command::SUCCESS;
            }

            $this->error('Backup failed: ' . implode("\n", $output));
            return Command::FAILURE;
        }

        $dbPath = database_path('database.sqlite');
        if (!file_exists($dbPath)) {
            $this->error('Database file not found.');
            return Command::FAILURE;
        }

        $destPath = $backupDir . DIRECTORY_SEPARATOR . $filename;
        if (copy($dbPath, $destPath)) {
            $this->info("Backup created: {$filename} (" . number_format(filesize($destPath) / 1024, 1) . ' KB)');
            return Command::SUCCESS;
        }

        $this->error('Backup failed.');
        return Command::FAILURE;
    }
}
