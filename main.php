#!/usr/bin/env php
<?php
// ============================================================
// main.php — LinkedIn Auto-Email System Entry Point
//
// Run manually:    php main.php
// Railway cron:    Configured in railway.json (runs every hour)
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/sent_log.php';
require_once __DIR__ . '/imap_fetch.php';
require_once __DIR__ . '/csv_fetch.php';
require_once __DIR__ . '/mailer.php';

// Ensure data directory exists
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}

log_info(str_repeat('=', 55));
log_info('LinkedIn Auto-Email System — Starting run');

// Validate config
if (EMAIL_ADDRESS === 'you@gmail.com' || EMAIL_PASSWORD === 'xxxx xxxx xxxx xxxx') {
    log_error('Please set EMAIL_ADDRESS and EMAIL_PASSWORD in config.php or Railway Variables!');
    exit(1);
}

// Load who we already emailed
$sent_log = load_sent_log();

// ── Gather applicants from all sources ───────────────────────
$applicants = [];

// Source 1: Gmail IMAP (LinkedIn notification emails)
$gmail_applicants = fetch_linkedin_applicants();
$applicants = array_merge($applicants, $gmail_applicants);

// Source 2: Manual CSV (LinkedIn export fallback)
$csv_applicants = load_csv_applicants();
$applicants = array_merge($applicants, $csv_applicants);

if (empty($applicants)) {
    log_info('No applicants found this run. Nothing to send.');
    exit(0);
}

log_info('Total applicants found: ' . count($applicants));

// ── Send emails ───────────────────────────────────────────────
$new_count = 0;

foreach ($applicants as $applicant) {
    $email = strtolower(trim($applicant['email'] ?? ''));

    if (empty($email)) {
        log_warn('Skipping applicant with no email.');
        continue;
    }

    if (already_sent($sent_log, $email)) {
        log_info("⏭  Already emailed: $email — skipping.");
        continue;
    }

    $success = send_email($applicant);

    if ($success) {
        mark_sent($sent_log, $applicant);
        $new_count++;
    }
}

// ── Save updated log ──────────────────────────────────────────
save_sent_log($sent_log);

log_info("Run complete. New emails sent: $new_count | Total logged: " . count($sent_log));
exit(0);
