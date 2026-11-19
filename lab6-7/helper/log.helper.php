<?php
declare(strict_types=1);

const LOG_LIMIT = 262144;
const LOG_FILES = ['activity' => 'activity.log', 'errors' => 'errors.log'];

function withLog(string $channel, callable $operation, bool $create = false): mixed
{
    $directory = getenv('SAWM_LOG_DIR') ?: dirname(__DIR__, 2) . '/.sawm-lab67-logs';
    if (!isset(LOG_FILES[$channel])) {
        return null;
    }

    if (!is_dir($directory)) {
        if (!$create) {
            return [];
        }
        if (!@mkdir($directory, 0700, true) && !is_dir($directory)) {
            return null;
        }
    }

    $path = $directory . '/' . LOG_FILES[$channel];
    $mask = umask(0077);
    try {
        $lock = @fopen($path . '.lock', 'c');
        if ($lock === false) {
            return null;
        }
        try {
            if (!@flock($lock, $create ? LOCK_EX : LOCK_SH)) {
                return null;
            }

            return $operation($path);
        } finally {
            fclose($lock);
        }
    } finally {
        umask($mask);
    }
}

function appendLog(string $channel, array $record): void
{
    $line = json_encode(['time' => gmdate('c')] + $record, JSON_INVALID_UTF8_SUBSTITUTE) . "\n";
    $written = withLog($channel, static function (string $path) use ($line): bool {
        clearstatcache(true, $path);
        if (is_file($path)) {
            $size = @filesize($path);
            if ($size === false || ($size + strlen($line) > LOG_LIMIT && !@rename($path, $path . '.1'))) {
                return false;
            }
        }

        return @file_put_contents($path, $line, FILE_APPEND) === strlen($line);
    }, true);

    if ($written !== true) {
        error_log('SAWM: could not write the ' . $channel . ' journal.');
    }
}

function audit(string $event, string $result, ?int $actor = null, ?int $target = null): void
{
    appendLog('activity', ['event' => $event, 'result' => $result, 'actor' => $actor, 'target' => $target]);
}

function recentLog(string $channel): ?array
{
    return withLog($channel, static function (string $path): ?array {
        if (!is_file($path)) {
            return [];
        }

        $contents = @file_get_contents($path, false, null, 0, LOG_LIMIT);
        if ($contents === false) {
            return null;
        }

        $lines = explode("\n", rtrim($contents, "\n"));

        return $contents === '' ? [] : array_reverse(array_slice($lines, -100));
    });
}
