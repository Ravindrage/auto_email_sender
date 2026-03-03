<?php
// ============================================================
// config.php — Edit YOUR settings here
// ============================================================

// --- Gmail SMTP credentials ---
define('EMAIL_ADDRESS',  getenv('EMAIL_ADDRESS')  ?: 'you@gmail.com');
define('EMAIL_PASSWORD', getenv('EMAIL_PASSWORD') ?: 'xxxx xxxx xxxx xxxx'); // Gmail App Password

// --- Company info ---
define('COMPANY_NAME', getenv('COMPANY_NAME') ?: 'Your Company Name');
define('JOB_TITLE',    getenv('JOB_TITLE')    ?: 'Software Engineer');

// --- Email subject template ---
define('EMAIL_SUBJECT', 'Thank you for applying — ' . JOB_TITLE . ' at ' . COMPANY_NAME);

// --- Email body template (use {name} and {job} as placeholders) ---
define('EMAIL_BODY', "
Hi {name},

Thank you for applying for the {job} position at " . COMPANY_NAME . "!

We have received your application and our team will review it shortly.

Here are some useful links to learn more about us:

🌐 Website      : https://yourwebsite.com
💼 LinkedIn     : https://linkedin.com/company/yourcompany
📄 Portfolio    : https://yourportfolio.com
📅 Book a Call  : https://calendly.com/yourlink
📧 Contact Us   : contact@yourcompany.com

We will get back to you within 3–5 business days.

Best regards,
Your Name
" . COMPANY_NAME . " Hiring Team
");

// --- Log file paths ---
define('SENT_LOG_FILE', __DIR__ . '/data/sent_emails.json');
define('APP_LOG_FILE',  __DIR__ . '/data/app.log');

// --- CSV fallback file ---
define('CSV_FILE', __DIR__ . '/applicants.csv');
