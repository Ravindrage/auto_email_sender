<?php
// ============================================================
// imap_fetch.php — Read LinkedIn applicant notification emails
//                  from Gmail via IMAP
// ============================================================

function fetch_linkedin_applicants(): array {
    $applicants = [];

    // Connect to Gmail IMAP
    $mailbox = '{imap.gmail.com:993/imap/ssl}INBOX';
    $conn    = @imap_open($mailbox, EMAIL_ADDRESS, EMAIL_PASSWORD);

    if (!$conn) {
        log_error('Gmail IMAP connection failed: ' . imap_last_error());
        log_warn('Tip: Make sure IMAP is enabled in Gmail settings and you are using an App Password.');
        return $applicants;
    }

    // Search for LinkedIn "new applicant" notification emails
    $ids = imap_search($conn, 'FROM "jobs-noreply@linkedin.com" SUBJECT "applied to your job"');

    if (!$ids) {
        log_info('No new LinkedIn applicant notifications found in Gmail.');
        imap_close($conn);
        return $applicants;
    }

    log_info('Found ' . count($ids) . ' LinkedIn notification email(s).');

    foreach ($ids as $id) {
        // Get plain text body
        $body    = imap_fetchbody($conn, $id, '1');  // Part 1 = plain text
        $headers = imap_headerinfo($conn, $id);
        $subject = isset($headers->subject) ? imap_utf8($headers->subject) : '';

        $applicant = parse_applicant($body, $subject);
        if ($applicant) {
            $applicants[] = $applicant;
        }
    }

    imap_close($conn);
    return $applicants;
}


function parse_applicant(string $body, string $subject): ?array {
    // Extract email addresses from body, skip LinkedIn system emails
    $skip_domains = ['linkedin.com', 'noreply', 'example.com'];
    preg_match_all('/[\w.\-]+@[\w.\-]+\.\w+/', $body, $email_matches);

    $applicant_email = null;
    foreach ($email_matches[0] as $email) {
        $skip = false;
        foreach ($skip_domains as $domain) {
            if (stripos($email, $domain) !== false) { $skip = true; break; }
        }
        if (!$skip) { $applicant_email = $email; break; }
    }

    // Extract name — LinkedIn format: "FirstName LastName applied"
    $name = 'Applicant';
    if (preg_match('/([A-Z][a-z]+ [A-Z][a-z]+)\s+applied/i', $body, $m)) {
        $name = $m[1];
    }

    // Extract job title from subject
    $job = JOB_TITLE;
    if (preg_match('/applied to your job[:\s]+(.+)/i', $subject, $m)) {
        $job = trim($m[1]);
    }

    if ($applicant_email) {
        return [
            'name'   => $name,
            'email'  => $applicant_email,
            'job'    => $job,
            'source' => 'gmail_imap',
        ];
    }

    return null;
}
