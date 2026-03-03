<?php
// ============================================================
// logger.php — Simple file + stdout logger
// ============================================================

function app_log(string $level, string $message): void {
    $line = date('[Y-m-d H:i:s]') . " [$level] $message" . PHP_EOL;
    echo $line;  // Railway captures stdout
    @file_put_contents(APP_LOG_FILE, $line, FILE_APPEND);
}

function log_info(string $msg)  { app_log('INFO',    $msg); }
function log_warn(string $msg)  { app_log('WARNING', $msg); }
function log_error(string $msg) { app_log('ERROR',   $msg); }
