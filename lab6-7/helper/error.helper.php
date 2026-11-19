<?php
declare(strict_types=1);

require_once __DIR__ . '/log.helper.php';

function recordAppError(string $severity, string $type, string $file, int $line, ?int $code = null): void
{
    appendLog('errors', [
        'severity' => $severity,
        'type' => $type,
        'code' => $code,
        'source' => basename($file),
        'line' => $line,
    ]);
}

function installErrorHandling(): void
{
    static $installed = false;
    if ($installed) {
        return;
    }
    $installed = true;

    ini_set('display_errors', '0');
    // Keep application diagnostics in the structured journal, not the default raw PHP log.
    ini_set('log_errors', '0');
    $originalBufferLevel = ob_get_level();
    ob_start();
    $failed = false;

    $showFallback = static function () use ($originalBufferLevel, &$failed): void {
        $failed = true;
        while (ob_get_level() > $originalBufferLevel) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code(500);
            header_remove('Location');
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-store');
        }
        readfile(__DIR__ . '/../errors/500.html');
    };

    set_error_handler(static function (int $level, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $level)) {
            return true;
        }
        recordAppError(in_array($level, [E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING], true)
            ? 'warning' : 'error', 'PHP', $file, $line, $level);
        if (in_array($level, [E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
            // The original error is already logged; use PHP's fatal handling and the shutdown hook.
            return false;
        }

        return true;
    });

    set_exception_handler(static function (Throwable $error) use ($showFallback): void {
        recordAppError('error', get_class($error), $error->getFile(), $error->getLine());
        $showFallback();
    });

    register_shutdown_function(static function () use ($showFallback, &$failed): void {
        $last = error_get_last();
        if ($failed || $last === null || !in_array($last['type'], [E_ERROR, E_PARSE, E_CORE_ERROR,
            E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
            return;
        }
        if (!in_array($last['type'], [E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
            recordAppError('fatal', 'PHP', $last['file'], $last['line'], $last['type']);
        }
        $showFallback();
    });
}
