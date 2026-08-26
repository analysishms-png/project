<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateEnvProduction extends Command
{
    protected $signature = 'env:validate {--file=.env.production : The env file to validate}';

    protected $description = 'Validate that all required environment variables are filled in for production deployment';

    // Variables that MUST have a real value (not placeholder)
    protected array $required = [
        'APP_KEY',
        'APP_URL',
        'DB_USERNAME',
        'DB_PASSWORD',
        'MAIL_HOST',
        'MAIL_USERNAME',
        'MAIL_PASSWORD',
        'MAIL_FROM_ADDRESS',
        'RAZORPAY_KEY_ID',
        'RAZORPAY_KEY_SECRET',
        'RAZORPAY_WEBHOOK_SECRET',
    ];

    // Variables that should be set but can have safe defaults
    protected array $recommended = [
        'REDIS_PASSWORD',
        'REVERB_APP_ID',
        'REVERB_APP_KEY',
        'REVERB_APP_SECRET',
        'REVERB_HOST',
    ];

    // Patterns that indicate a placeholder / unfilled value
    protected array $placeholderPatterns = [
        '/^your[_-]/i',
        '/your-domain/i',
        '/XXXXXXXX/i',
        '/CHANGE_ME/i',
        '/example\.com/i',
        '/placeholder/i',
        '/@example\./i',
    ];

    public function handle(): int
    {
        $file = $this->option('file');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $env = $this->parseEnvFile($file);

        $this->info("╔══════════════════════════════════════════════════╗");
        $this->info("║       Production Environment Validation         ║");
        $this->info("╚══════════════════════════════════════════════════╝");
        $this->newLine();
        $this->line("File: <info>{$file}</info>");
        $this->newLine();

        $errors = 0;
        $warnings = 0;

        // Check required variables
        $this->line("<comment>── Required Variables ──</comment>");
        foreach ($this->required as $var) {
            $value = $env[$var] ?? null;
            if ($value === null) {
                $this->error("  ✗ {$var} — MISSING");
                $errors++;
            } elseif ($this->isPlaceholder($value)) {
                $this->error("  ✗ {$var} — still placeholder: {$value}");
                $errors++;
            } else {
                $this->info("  ✓ {$var}");
            }
        }

        $this->newLine();

        // Check recommended variables
        $this->line("<comment>── Recommended Variables ──</comment>");
        foreach ($this->recommended as $var) {
            $value = $env[$var] ?? null;
            if ($value === null || $value === '') {
                $this->warn("  ⚠ {$var} — not set (using default)");
                $warnings++;
            } elseif ($this->isPlaceholder($value)) {
                $this->warn("  ⚠ {$var} — still placeholder: {$value}");
                $warnings++;
            } else {
                $this->info("  ✓ {$var}");
            }
        }

        $this->newLine();

        // Summary
        $this->line("<comment>── Summary ──</comment>");
        if ($errors === 0 && $warnings === 0) {
            $this->info("  All checks passed! Ready to deploy.");
        } else {
            if ($errors > 0) {
                $this->error("  {$errors} error(s) — must fix before deploying");
            }
            if ($warnings > 0) {
                $this->warn("  {$warnings} warning(s) — review recommended");
            }
        }

        $this->newLine();

        return $errors > 0 ? 1 : 0;
    }

    protected function parseEnvFile(string $path): array
    {
        $vars = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $value = trim($value);

                if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                    $vars[trim($key)] = trim($value, "\"'");
                    continue;
                }

                // Strip inline comments for unquoted values.
                $commentPos = strpos($value, ' #');
                if ($commentPos !== false) {
                    $value = substr($value, 0, $commentPos);
                }

                $vars[trim($key)] = trim($value);
            }
        }

        return $vars;
    }

    protected function isPlaceholder(string $value): bool
    {
        foreach ($this->placeholderPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        return false;
    }
}
