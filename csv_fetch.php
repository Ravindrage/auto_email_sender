<?php
// ============================================================
// csv_fetch.php — Load applicants from manually exported CSV
//
// CSV format (first row = headers):
//   name,email,job_title
//   John Smith,john@example.com,Software Engineer
// ============================================================

function load_csv_applicants(): array {
    if (!file_exists(CSV_FILE)) return [];

    $applicants = [];
    $handle     = fopen(CSV_FILE, 'r');
    $headers    = fgetcsv($handle);  // skip header row

    // Normalize headers
    $headers = array_map('strtolower', array_map('trim', $headers));

    while (($row = fgetcsv($handle)) !== false) {
        $data = array_combine($headers, $row);
        $email = trim($data['email'] ?? '');
        if (!$email) continue;

        $applicants[] = [
            'name'   => trim($data['name']      ?? 'Applicant'),
            'email'  => $email,
            'job'    => trim($data['job_title'] ?? JOB_TITLE),
            'source' => 'csv',
        ];
    }

    fclose($handle);
    log_info('Loaded ' . count($applicants) . ' applicant(s) from CSV.');
    return $applicants;
}
