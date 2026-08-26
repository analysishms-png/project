<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class DatabaseBackupController extends Controller
{
    protected $propertyid;
    protected $backupPath;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->propertyid = session('propertyid') ?? Auth::user()->propertyid ?? 0;
            return $next($request);
        });
        $this->backupPath = storage_path('app/backups');
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // BACKUP DASHBOARD — List backups, create new, schedule
    // ═══════════════════════════════════════════════════════════════
    public function index()
    {
        $backups = collect();
        if (File::exists($this->backupPath)) {
            $files = File::files($this->backupPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'sql' || $file->getExtension() === 'gz') {
                    $backups->push([
                        'name' => $file->getFilename(),
                        'size' => $this->formatSize($file->getSize()),
                        'size_bytes' => $file->getSize(),
                        'date' => date('Y-m-d H:i:s', $file->getMTime()),
                        'path' => $file->getPathname(),
                    ]);
                }
            }
            $backups = $backups->sortByDesc('date')->values();
        }

        // Database info
        $dbName = DB::getDatabaseName();
        $tableCount = DB::select("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = ?", [$dbName])[0]->cnt ?? 0;

        return view('property.backup.dashboard', compact('backups', 'dbName', 'tableCount'));
    }

    // ═══════════════════════════════════════════════════════════════
    // CREATE BACKUP — Generate MySQL dump
    // ═══════════════════════════════════════════════════════════════
    public function createBackup(Request $request)
    {
        $dbName = DB::getDatabaseName();
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$dbName}_{$timestamp}.sql";
        $filepath = $this->backupPath . '/' . $filename;

        // Try mysqldump
        $mysqlPath = $this->findMysqlPath();
        if ($mysqlPath) {
            $cmd = "{$mysqlPath}mysqldump -u root {$dbName} > \"{$filepath}\" 2>&1";
            exec($cmd, $output, $returnVar);

            if ($returnVar === 0 && File::exists($filepath)) {
                // Compress
                $gzFile = $filepath . '.gz';
                $content = file_get_contents($filepath);
                $gzContent = gzencode($content);
                file_put_contents($gzFile, $gzContent);
                File::delete($filepath);

                return response()->json([
                    'success' => true,
                    'message' => 'Backup created: ' . basename($gzFile),
                    'filename' => basename($gzFile),
                    'size' => $this->formatSize(File::size($gzFile)),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'mysqldump failed. Output: ' . implode("\n", $output)
            ]);
        }

        // Fallback: PHP-based dump (slower, for shared hosting)
        return $this->createPhpBackup($dbName, $timestamp);
    }

    // ═══════════════════════════════════════════════════════════════
    // DOWNLOAD BACKUP
    // ═══════════════════════════════════════════════════════════════
    public function downloadBackup($filename)
    {
        $filepath = $this->backupPath . '/' . $filename;
        if (!File::exists($filepath)) {
            return redirect()->back()->with('error', 'Backup file not found');
        }

        return response()->download($filepath, $filename, [
            'Content-Type' => $filename.endsWith('.gz') ? 'application/gzip' : 'text/plain',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DELETE BACKUP
    // ═══════════════════════════════════════════════════════════════
    public function deleteBackup($filename)
    {
        $filepath = $this->backupPath . '/' . $filename;
        if (File::exists($filepath)) {
            File::delete($filepath);
        }
        return redirect()->route('backup.index')->with('success', 'Backup deleted');
    }

    // ═══════════════════════════════════════════════════════════════
    // SCHEDULE BACKUP — Set up daily/weekly backup via cron
    // ═══════════════════════════════════════════════════════════════
    public function scheduleSettings()
    {
        $scheduleFile = storage_path('app/backup_schedule.json');
        $schedule = File::exists($scheduleFile) ? json_decode(File::get($scheduleFile), true) : [
            'enabled' => false,
            'frequency' => 'daily',
            'time' => '02:00',
            'keep_days' => 30,
        ];

        return view('property.backup.schedule', compact('schedule'));
    }

    public function saveSchedule(Request $request)
    {
        $request->validate([
            'frequency' => 'required|in:daily,weekly,monthly',
            'time' => 'required',
            'keep_days' => 'required|integer|min:1|max:365',
        ]);

        $schedule = [
            'enabled' => $request->input('enabled', false),
            'frequency' => $request->frequency,
            'time' => $request->time,
            'keep_days' => $request->keep_days,
            'last_run' => null,
        ];

        File::put(storage_path('app/backup_schedule.json'), json_encode($schedule, JSON_PRETTY_PRINT));

        return redirect()->route('backup.schedule')->with('success', 'Backup schedule saved');
    }

    // ═══════════════════════════════════════════════════════════════
    // CRON JOB — Called by artisan schedule or system cron
    // ═══════════════════════════════════════════════════════════════
    public function runScheduledBackup()
    {
        $scheduleFile = storage_path('app/backup_schedule.json');
        if (!File::exists($scheduleFile)) return;

        $schedule = json_decode(File::get($scheduleFile), true);
        if (!$schedule['enabled']) return;

        // Check if backup is due
        $lastRun = $schedule['last_run'] ?? null;
        $now = now();
        $isDue = false;

        if (!$lastRun) {
            $isDue = true;
        } else {
            $last = strtotime($lastRun);
            switch ($schedule['frequency']) {
                case 'daily':
                    $isDue = (date('Y-m-d', $last) < date('Y-m-d'));
                    break;
                case 'weekly':
                    $isDue = (date('Y-m-d', $last) < date('Y-m-d', strtotime('-7 days')));
                    break;
                case 'monthly':
                    $isDue = (date('Y-m', $last) < date('Y-m'));
                    break;
            }
        }

        if ($isDue) {
            // Create backup
            $dbName = DB::getDatabaseName();
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "scheduled_{$dbName}_{$timestamp}.sql";
            $filepath = $this->backupPath . '/' . $filename;

            $mysqlPath = $this->findMysqlPath();
            if ($mysqlPath) {
                exec("{$mysqlPath}mysqldump -u root {$dbName} > \"{$filepath}\" 2>&1", $output, $returnVar);

                if ($returnVar === 0 && File::exists($filepath)) {
                    $gzFile = $filepath . '.gz';
                    file_put_contents($gzFile, gzencode(file_get_contents($filepath)));
                    File::delete($filepath);
                }
            }

            // Update last run
            $schedule['last_run'] = $now->toDateTimeString();
            File::put($scheduleFile, json_encode($schedule, JSON_PRETTY_PRINT));

            // Cleanup old backups
            $this->cleanupOldBackups($schedule['keep_days']);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════
    private function findMysqlPath()
    {
        $paths = ['/c/xampp/mysql/bin/', 'C:\\xampp\\mysql\\bin\\', '/usr/bin/', '/usr/local/bin/'];
        foreach ($paths as $path) {
            if (file_exists($path . 'mysqldump')) return $path;
        }
        return null;
    }

    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < 3) { $bytes /= 1024; $i++; }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    private function cleanupOldBackups($keepDays)
    {
        $files = File::files($this->backupPath);
        $cutoff = strtotime("-{$keepDays} days");
        foreach ($files as $file) {
            if ($file->getMTime() < $cutoff) {
                File::delete($file->getPathname());
            }
        }
    }

    private function createPhpBackup($dbName, $timestamp)
    {
        $filename = "backup_{$dbName}_{$timestamp}.sql";
        $filepath = $this->backupPath . '/' . $filename;

        $tables = DB::select("SHOW TABLES FROM `" . str_replace('`', '', $dbName) . "`");
        $sql = "-- Analysis HMS Backup\n-- Database: {$dbName}\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = reset($table);
            $safeName = str_replace('`', '', $tableName);
            $sql .= "DROP TABLE IF EXISTS `{$safeName}`;\n";
            $create = DB::select("SHOW CREATE TABLE `{$safeName}`");
            $sql .= $create[0]->{'Create Table'} . ";\n\n";

            $rows = DB::select("SELECT * FROM `{$safeName}`");
            foreach ($rows as $row) {
                $values = array_map(function ($v) { return $v === null ? 'NULL' : "'" . addslashes($v) . "'"; }, (array) $row);
                $sql .= "INSERT INTO `{$tableName}` VALUES(" . implode(',', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        file_put_contents($filepath, $sql);

        return response()->json([
            'success' => true,
            'message' => 'Backup created (PHP method): ' . $filename,
            'filename' => $filename,
            'size' => $this->formatSize(File::size($filepath)),
        ]);
    }
}
