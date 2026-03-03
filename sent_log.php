<?php
// ============================================================
// sent_log.php — Track emails already sent (avoid duplicates)
// ============================================================

function load_sent_log(): array {
    if (!file_exists(SENT_LOG_FILE)) return [];
    $json = file_get_contents(SENT_LOG_FILE);
    return json_decode($json, true) ?: [];
}

function save_sent_log(array $log): void {
    $dir = dirname(SENT_LOG_FILE);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents(SENT_LOG_FILE, json_encode($log, JSON_PRETTY_PRINT));
}

function already_sent(array $log, string $email): bool {
    return isset($log[strtolower(trim($email))]);
}

function mark_sent(array &$log, array $applicant): void {
    $log[strtolower(trim($applicant['email']))] = [
        'name'    => $applicant['name'],
        'job'     => $applicant['job'],
        'sent_at' => date('Y-m-d H:i:s'),
        'source'  => $applicant['source'] ?? 'unknown',
    ];
}
