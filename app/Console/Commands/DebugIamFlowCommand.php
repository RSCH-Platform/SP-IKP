<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DebugIamFlowCommand extends Command
{
    protected $signature = 'debug:iam-flow {--live : Watch logs in real-time} {--clear : Clear debug logs before running}';
    protected $description = 'Debug IAM SSO flow with detailed request tracing';

    public function handle()
    {
        if ($this->option('clear')) {
            $this->clearDebugLogs();
        }

        if ($this->option('live')) {
            $this->watchLogs();
        } else {
            $this->analyzeLogs();
        }
    }

    private function watchLogs(): void
    {
        $this->info('🔍 Watching IAM debug logs in real-time...');
        $this->info('Press Ctrl+C to stop');
        $this->line('');

        $logFile = storage_path('logs/debug.log');
        $handle = fopen($logFile, 'r');
        fseek($handle, 0, SEEK_END);

        while (true) {
            $line = fgets($handle);
            if ($line !== false) {
                $this->processAndDisplayLine($line);
            } else {
                usleep(500000); // Sleep 500ms before checking again
            }
        }
    }

    private function analyzeLogs(): void
    {
        $logFile = storage_path('logs/debug.log');
        
        if (!file_exists($logFile)) {
            $this->error('Debug log file not found: ' . $logFile);
            return;
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        $analysis = $this->analyzeLogLines($lines);

        $this->displayAnalysis($analysis);
    }

    private function analyzeLogLines(array $lines): array
    {
        $analysis = [
            'total_requests' => 0,
            'redirects' => 0,
            'potential_loops' => 0,
            'auth_changes' => 0,
            'session_changes' => 0,
            'token_errors' => 0,
            'requests' => [],
            'auth_timeline' => [],
            'redirect_chain' => [],
        ];

        foreach ($lines as $line) {
            if (strpos($line, '[ADVANCED_DEBUG]') !== false) {
                $this->processAdvancedDebugLine($line, $analysis);
            } elseif (strpos($line, '[TOKEN_DEBUG]') !== false) {
                $this->processTokenDebugLine($line, $analysis);
            }
        }

        return $analysis;
    }

    private function processAdvancedDebugLine(string $line, array &$analysis): void
    {
        if (strpos($line, 'REQUEST_START') !== false) {
            $analysis['total_requests']++;
        } elseif (strpos($line, 'REDIRECT_DETECTED') !== false) {
            $analysis['redirects']++;
            preg_match('/from_url.*?=>.*?"([^"]*)"/', $line, $matches);
            if (!empty($matches[1])) {
                $analysis['redirect_chain'][] = $matches[1];
            }
        } elseif (strpos($line, 'potential_loop.*?true') !== false) {
            $analysis['potential_loops']++;
        }

        if (strpos($line, 'auth_status_before.*?false') !== false && 
            strpos($line, 'auth_status_after.*?true') !== false) {
            $analysis['auth_changes']++;
        }

        if (strpos($line, 'session_changed.*?true') !== false) {
            $analysis['session_changes']++;
        }
    }

    private function processTokenDebugLine(string $line, array &$analysis): void
    {
        if (strpos($line, 'TOKEN_VERIFICATION_FAILED') !== false || 
            strpos($line, 'TOKEN_DECODE_ERROR') !== false) {
            $analysis['token_errors']++;
        }
    }

    private function displayAnalysis(array $analysis): void
    {
        $this->line('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('                  IAM FLOW DEBUG ANALYSIS');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->line('');

        $this->displayMetrics($analysis);
        $this->line('');
        $this->displayRedirectChain($analysis);
        $this->line('');
        $this->displayRecommendations($analysis);
    }

    private function displayMetrics(array $analysis): void
    {
        $this->table(['Metric', 'Value', 'Status'], [
            ['Total Requests', $analysis['total_requests'], $this->getStatus($analysis['total_requests'] > 0)],
            ['Redirects', $analysis['redirects'], $this->getStatus($analysis['redirects'] > 0)],
            ['Potential Loops', $analysis['potential_loops'], $this->getStatus($analysis['potential_loops'] === 0, true)],
            ['Auth Status Changes', $analysis['auth_changes'], $this->getStatus($analysis['auth_changes'] > 0)],
            ['Session Changes', $analysis['session_changes'], $this->getStatus($analysis['session_changes'] > 0)],
            ['Token Errors', $analysis['token_errors'], $this->getStatus($analysis['token_errors'] === 0, true)],
        ]);
    }

    private function displayRedirectChain(array $analysis): void
    {
        if (empty($analysis['redirect_chain'])) {
            $this->info('✓ No redirect chains detected');
            return;
        }

        $this->warn('⚠ Redirect Chain Detected:');
        foreach ($analysis['redirect_chain'] as $index => $url) {
            $this->line(str_repeat('  ', $index) . '→ ' . substr($url, -50));
        }
    }

    private function displayRecommendations(array $analysis): void
    {
        $this->info('📋 Recommendations:');

        if ($analysis['potential_loops'] > 0) {
            $this->warn('  • [CRITICAL] Potential redirect loops detected!');
            $this->warn('    - Check IAM token generation and validation');
            $this->warn('    - Verify session state consistency');
            $this->warn('    - Check middleware execution order');
        }

        if ($analysis['token_errors'] > 0) {
            $this->warn('  • [WARNING] Token verification errors detected');
            $this->warn('    - Check IAM secret configuration');
            $this->warn('    - Verify JWT algorithm');
        }

        if ($analysis['session_changes'] === 0 && $analysis['redirects'] > 0) {
            $this->warn('  • [WARNING] No session changes during redirects');
            $this->warn('    - Session might not be persisting correctly');
            $this->warn('    - Check database session configuration');
        }

        if ($analysis['auth_changes'] === 0 && $analysis['redirects'] > 0) {
            $this->warn('  • [INFO] No authentication changes during redirects');
            $this->warn('    - User might be stuck in unauthenticated state');
        }
    }

    private function getStatus(bool $isOk, bool $inverted = false): string
    {
        $ok = $inverted ? !$isOk : $isOk;
        return $ok ? '<fg=green>✓</>' : '<fg=red>✗</>';
    }

    private function clearDebugLogs(): void
    {
        $logFile = storage_path('logs/debug.log');
        if (file_exists($logFile)) {
            unlink($logFile);
            $this->info('Debug logs cleared');
        }
    }

    private function processAndDisplayLine(string $line): void
    {
        if (strpos($line, 'REQUEST_START') !== false) {
            $this->line('<fg=cyan>→ REQUEST START</>');
        } elseif (strpos($line, 'REDIRECT_DETECTED') !== false) {
            $this->line('<fg=yellow>⟳ REDIRECT DETECTED</>');
        } elseif (strpos($line, 'TOKEN_VALID') !== false) {
            $this->line('<fg=green>✓ TOKEN VALID</>');
        } elseif (strpos($line, 'TOKEN_VERIFICATION_FAILED') !== false) {
            $this->line('<fg=red>✗ TOKEN VERIFICATION FAILED</>');
        }
    }
}
