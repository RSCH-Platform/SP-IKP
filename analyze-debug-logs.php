<?php

/**
 * Advanced IAM Flow Debug Log Analyzer
 * 
 * Usage:
 *   php analyze-debug-logs.php
 *   php analyze-debug-logs.php --live (real-time monitoring)
 *   php analyze-debug-logs.php --loop (detect loops)
 *   php analyze-debug-logs.php --tokens (analyze tokens)
 */

class IamDebugAnalyzer
{
    private string $logFile;
    private array $requests = [];
    private array $tokens = [];
    private array $redirects = [];
    private array $loops = [];

    public function __construct()
    {
        $this->logFile = __DIR__ . '/storage/logs/debug.log';
    }

    public function analyze(): void
    {
        if (!file_exists($this->logFile)) {
            echo "❌ Debug log file not found: {$this->logFile}\n";
            return;
        }

        $this->parseLog();
        $this->detectLoops();
        $this->displayAnalysis();
    }

    private function parseLog(): void
    {
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos($line, '[ADVANCED_DEBUG]') !== false) {
                $this->parseAdvancedDebugLine($line);
            } elseif (strpos($line, '[TOKEN_DEBUG]') !== false) {
                $this->parseTokenDebugLine($line);
            }
        }
    }

    private function parseAdvancedDebugLine(string $line): void
    {
        if (strpos($line, 'REQUEST_START') !== false) {
            preg_match('/"request_id":"([^"]+)"/', $line, $matches);
            $requestId = $matches[1] ?? uniqid();
            
            preg_match('/"path":"([^"]+)"/', $line, $matches);
            $path = $matches[1] ?? 'unknown';
            
            preg_match('/"authenticated_before":(true|false)/', $line, $matches);
            $authBefore = $matches[1] === 'true';

            $this->requests[$requestId] = [
                'path' => $path,
                'auth_before' => $authBefore,
                'status' => null,
                'redirect_to' => null,
                'duration' => 0,
            ];
        } elseif (strpos($line, 'REQUEST_END') !== false) {
            preg_match('/"request_id":"([^"]+)"/', $line, $matches);
            $requestId = $matches[1] ?? null;

            if ($requestId && isset($this->requests[$requestId])) {
                preg_match('/"status_code":(\d+)/', $line, $matches);
                $this->requests[$requestId]['status'] = (int)$matches[1];

                preg_match('/"redirect_to":"([^"]*)"/', $line, $matches);
                if (!empty($matches[1])) {
                    $this->requests[$requestId]['redirect_to'] = $matches[1];
                }

                preg_match('/"duration_ms":([\d.]+)/', $line, $matches);
                $this->requests[$requestId]['duration'] = (float)$matches[1];
            }
        } elseif (strpos($line, 'REDIRECT_DETECTED') !== false) {
            preg_match('/"from_url":"([^"]+)"/', $line, $matches);
            $from = $matches[1] ?? 'unknown';

            preg_match('/"redirect_to":"([^"]+)"/', $line, $matches);
            $to = $matches[1] ?? 'unknown';

            preg_match('/"potential_loop":(true|false)/', $line, $matches);
            $isLoop = $matches[1] === 'true';

            $this->redirects[] = [
                'from' => $from,
                'to' => $to,
                'is_loop' => $isLoop,
            ];
        }
    }

    private function parseTokenDebugLine(string $line): void
    {
        if (strpos($line, 'TOKEN_STRUCTURE') !== false) {
            preg_match('/"request_id":"([^"]+)"/', $line, $matches);
            $requestId = $matches[1] ?? 'unknown';

            preg_match('/"sub":(\d+)/', $line, $matches);
            $sub = $matches[1] ?? 'unknown';

            preg_match('/"exp":(\d+)/', $line, $matches);
            $exp = $matches[1] ?? null;

            $this->tokens[$requestId] = [
                'sub' => $sub,
                'exp' => $exp,
                'valid' => $exp && $exp > time(),
            ];
        }
    }

    private function detectLoops(): void
    {
        $prevPath = null;
        $loopCount = 0;

        foreach ($this->requests as $requestId => $request) {
            if ($prevPath === $request['path'] && $request['path'] !== '/sso/callback') {
                $loopCount++;
            } else {
                if ($loopCount > 2) {
                    $this->loops[] = [
                        'path' => $prevPath,
                        'count' => $loopCount,
                    ];
                }
                $loopCount = 1;
            }
            $prevPath = $request['path'];
        }
    }

    private function displayAnalysis(): void
    {
        $this->displayHeader();
        $this->displayMetrics();
        $this->displayRequestFlow();
        $this->displayRedirectChain();
        $this->displayTokenAnalysis();
        $this->displayLoopAnalysis();
        $this->displayRecommendations();
    }

    private function displayHeader(): void
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║           IAM DEBUG LOG ANALYSIS & DIAGNOSTICS            ║\n";
        echo "║                    Advanced Debug Report                   ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";
    }

    private function displayMetrics(): void
    {
        echo "📊 METRICS\n";
        echo "─────────────────────────────────────────────────────────────\n";
        echo sprintf("  Total Requests:        %d\n", count($this->requests));
        echo sprintf("  Redirects:             %d\n", count($this->redirects));
        echo sprintf("  Potential Loops:       %d\n", count(array_filter($this->redirects, fn($r) => $r['is_loop'])));
        echo sprintf("  Token Events:          %d\n", count($this->tokens));
        echo sprintf("  Detected Loop Chains:  %d\n", count($this->loops));
        echo "\n";
    }

    private function displayRequestFlow(): void
    {
        echo "🔄 REQUEST FLOW\n";
        echo "─────────────────────────────────────────────────────────────\n";

        $requestNum = 0;
        foreach ($this->requests as $requestId => $request) {
            $requestNum++;
            $status = $this->getStatusEmoji($request['status'] ?? 200);
            $redirectInfo = $request['redirect_to'] ? ' → ' . basename($request['redirect_to']) : '';

            echo sprintf("  %2d. %s %-20s [%d] {%.2fms}%s\n",
                $requestNum,
                $status,
                basename($request['path']),
                $request['status'] ?? 0,
                $request['duration'],
                $redirectInfo
            );
        }
        echo "\n";
    }

    private function displayRedirectChain(): void
    {
        if (empty($this->redirects)) {
            echo "✓ No redirects detected\n\n";
            return;
        }

        echo "↪️  REDIRECT CHAIN\n";
        echo "─────────────────────────────────────────────────────────────\n";

        foreach ($this->redirects as $index => $redirect) {
            $loopMarker = $redirect['is_loop'] ? ' ⚠️  [LOOP]' : '';
            echo sprintf("  %d. %s → %s%s\n",
                $index + 1,
                basename(parse_url($redirect['from'], PHP_URL_PATH) ?? $redirect['from']),
                basename(parse_url($redirect['to'], PHP_URL_PATH) ?? $redirect['to']),
                $loopMarker
            );
        }
        echo "\n";
    }

    private function displayTokenAnalysis(): void
    {
        if (empty($this->tokens)) {
            echo "❌ No token events found\n\n";
            return;
        }

        echo "🔐 TOKEN ANALYSIS\n";
        echo "─────────────────────────────────────────────────────────────\n";

        foreach ($this->tokens as $requestId => $token) {
            $validMarker = $token['valid'] ? '✓ VALID' : '✗ EXPIRED';
            $expTime = $token['exp'] ? date('Y-m-d H:i:s', $token['exp']) : 'unknown';

            echo sprintf("  User: %s | Exp: %s | %s\n",
                $token['sub'],
                $expTime,
                $validMarker
            );
        }
        echo "\n";
    }

    private function displayLoopAnalysis(): void
    {
        if (empty($this->loops)) {
            echo "✓ No redirect loops detected\n\n";
            return;
        }

        echo "🔁 REDIRECT LOOP ANALYSIS\n";
        echo "─────────────────────────────────────────────────────────────\n";

        foreach ($this->loops as $loop) {
            echo sprintf("  ⚠️  Detected %dx loop on %s\n", $loop['count'], $loop['path']);
        }
        echo "\n";
    }

    private function displayRecommendations(): void
    {
        echo "💡 RECOMMENDATIONS\n";
        echo "─────────────────────────────────────────────────────────────\n";

        $issues = [];

        if (count($this->loops) > 0) {
            $issues[] = "Redirect loops detected - check token generation or session state";
        }

        if (count(array_filter($this->redirects, fn($r) => $r['is_loop'])) > 0) {
            $issues[] = "Potential circular redirects - verify IAM configuration";
        }

        if (empty($this->tokens)) {
            $issues[] = "No tokens detected - verify IAM integration is working";
        }

        if (empty($issues)) {
            echo "  ✓ No critical issues detected\n";
        } else {
            foreach ($issues as $issue) {
                echo "  • $issue\n";
            }
        }

        echo "\n";
    }

    private function getStatusEmoji(int $status): string
    {
        if ($status >= 200 && $status < 300) return '✓';
        if ($status >= 300 && $status < 400) return '→';
        if ($status >= 400 && $status < 500) return '⚠';
        if ($status >= 500) return '✗';
        return '?';
    }
}

// Run analyzer
$analyzer = new IamDebugAnalyzer();
$analyzer->analyze();
