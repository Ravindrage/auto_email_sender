# ============================================================
# config.py — Edit all your settings here
# ============================================================

# --- Your Gmail credentials ---
EMAIL_ADDRESS = "you@gmail.com"          # Your Gmail address
EMAIL_PASSWORD = "xxxx xxxx xxxx xxxx"   # Gmail App Password (NOT your real password)
                                          # Get it at: myaccount.google.com/apppasswords

# --- LinkedIn credentials (to check application notification emails) ---
LINKEDIN_EMAIL = "you@gmail.com"         # Email linked to your LinkedIn account
# NOTE: We will READ LinkedIn notification emails from Gmail to detect new applicants

# --- Your custom email template ---
EMAIL_SUBJECT = "Thank you for applying — {job_title} at {company_name}"

EMAIL_BODY = """
Hi {applicant_name},

Thank you for applying for the {job_title} position at {company_name}!

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
{company_name}
{job_title} Hiring Team
"""

# --- Your company info ---
COMPANY_NAME = "Your Company Name"
JOB_TITLE    = "Software Engineer"       # Update per job post

# --- How often to check for new applicants (in seconds) ---
# PythonAnywhere free plan: use scheduled tasks (every 1 hour minimum)
CHECK_INTERVAL_SECONDS = 3600  # 1 hour

# --- Track who already received email (avoid duplicates) ---
SENT_LOG_FILE = "sent_emails.json"
