#!/usr/bin/env python3
"""
LinkedIn Job Application Auto-Email System
==========================================
How it works:
1. Reads your Gmail inbox for LinkedIn "New applicant" notification emails
2. Extracts applicant name + email from those notifications
3. Sends your custom email to each new applicant
4. Logs sent emails to avoid duplicates

Setup on PythonAnywhere:
- Upload this folder to PythonAnywhere Files
- Install requirements: pip install --user imapclient
- Schedule main.py as a Scheduled Task (every 1 hour)
"""

import imaplib
import smtplib
import email
import json
import os
import re
import logging
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from datetime import datetime

from config import (
    EMAIL_ADDRESS, EMAIL_PASSWORD,
    EMAIL_SUBJECT, EMAIL_BODY,
    COMPANY_NAME, JOB_TITLE,
    SENT_LOG_FILE
)

# --- Logging setup ---
logging.basicConfig(
    filename="auto_email.log",
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s"
)
log = logging.getLogger(__name__)


# ============================================================
# 1. LOAD / SAVE SENT EMAIL LOG
# ============================================================

def load_sent_log():
    """Load list of emails we already sent to."""
    if os.path.exists(SENT_LOG_FILE):
        with open(SENT_LOG_FILE, "r") as f:
            return json.load(f)
    return {}

def save_sent_log(log_data):
    with open(SENT_LOG_FILE, "w") as f:
        json.dump(log_data, f, indent=2)


# ============================================================
# 2. FETCH LINKEDIN NOTIFICATION EMAILS FROM GMAIL
# ============================================================

def fetch_linkedin_applicants():
    """
    Connect to Gmail via IMAP and find LinkedIn 'new applicant' emails.
    Returns list of dicts: [{"name": ..., "email": ..., "job": ...}]
    """
    applicants = []

    try:
        mail = imaplib.IMAP4_SSL("imap.gmail.com")
        mail.login(EMAIL_ADDRESS, EMAIL_PASSWORD)
        mail.select("inbox")

        # Search for LinkedIn notification emails about new applicants
        # LinkedIn sends from: jobs-noreply@linkedin.com
        _, message_ids = mail.search(
            None,
            '(FROM "jobs-noreply@linkedin.com" SUBJECT "applied to your job")'
        )

        ids = message_ids[0].split()
        log.info(f"Found {len(ids)} LinkedIn applicant notification emails.")

        for msg_id in ids:
            _, msg_data = mail.fetch(msg_id, "(RFC822)")
            raw_email = msg_data[0][1]
            msg = email.message_from_bytes(raw_email)

            # Extract text body
            body = ""
            if msg.is_multipart():
                for part in msg.walk():
                    if part.get_content_type() == "text/plain":
                        body = part.get_payload(decode=True).decode("utf-8", errors="ignore")
                        break
            else:
                body = msg.get_payload(decode=True).decode("utf-8", errors="ignore")

            # Parse applicant info from email body
            applicant = parse_applicant_from_email(body, msg)
            if applicant:
                applicants.append(applicant)

        mail.logout()

    except Exception as e:
        log.error(f"IMAP error: {e}")

    return applicants


def parse_applicant_from_email(body, msg):
    """
    Extract applicant name and email from LinkedIn notification email body.
    LinkedIn notification format includes applicant details.
    """
    try:
        # Try to find applicant email in body
        email_pattern = r'[\w\.-]+@[\w\.-]+\.\w+'
        emails_found = re.findall(email_pattern, body)

        # Filter out LinkedIn's own emails and common system emails
        skip_domains = ["linkedin.com", "noreply", "example.com"]
        applicant_email = None
        for e in emails_found:
            if not any(skip in e for skip in skip_domains):
                applicant_email = e
                break

        # Try to extract name — LinkedIn usually puts "FirstName LastName applied"
        name_match = re.search(r'([A-Z][a-z]+ [A-Z][a-z]+)\s+applied', body)
        applicant_name = name_match.group(1) if name_match else "Applicant"

        # Try to extract job title from email subject
        subject = msg.get("Subject", "")
        job_match = re.search(r'applied to your job[:\s]+(.+)', subject, re.IGNORECASE)
        job = job_match.group(1).strip() if job_match else JOB_TITLE

        if applicant_email:
            return {
                "name": applicant_name,
                "email": applicant_email,
                "job": job,
                "source": "gmail_notification"
            }

    except Exception as e:
        log.warning(f"Could not parse applicant from email: {e}")

    return None


# ============================================================
# 3. ADD APPLICANTS MANUALLY (CSV fallback)
# ============================================================

def load_manual_applicants(csv_file="applicants.csv"):
    """
    Fallback: manually export applicants from LinkedIn and drop CSV here.
    CSV format: name,email,job_title
    """
    applicants = []
    if not os.path.exists(csv_file):
        return applicants

    import csv
    with open(csv_file, "r") as f:
        reader = csv.DictReader(f)
        for row in reader:
            applicants.append({
                "name": row.get("name", "Applicant"),
                "email": row.get("email", ""),
                "job": row.get("job_title", JOB_TITLE),
                "source": "csv"
            })
    log.info(f"Loaded {len(applicants)} applicants from CSV.")
    return applicants


# ============================================================
# 4. SEND CUSTOM EMAIL
# ============================================================

def send_email(applicant):
    """Send custom auto-reply email to applicant."""
    name  = applicant["name"]
    to    = applicant["email"]
    job   = applicant.get("job", JOB_TITLE)

    subject = EMAIL_SUBJECT.format(
        applicant_name=name,
        job_title=job,
        company_name=COMPANY_NAME
    )
    body = EMAIL_BODY.format(
        applicant_name=name,
        job_title=job,
        company_name=COMPANY_NAME
    )

    msg = MIMEMultipart("alternative")
    msg["Subject"] = subject
    msg["From"]    = EMAIL_ADDRESS
    msg["To"]      = to
    msg.attach(MIMEText(body, "plain"))

    try:
        with smtplib.SMTP_SSL("smtp.gmail.com", 465) as server:
            server.login(EMAIL_ADDRESS, EMAIL_PASSWORD)
            server.sendmail(EMAIL_ADDRESS, to, msg.as_string())
        log.info(f"✅ Email sent to {name} <{to}> for job: {job}")
        return True
    except Exception as e:
        log.error(f"❌ Failed to send email to {to}: {e}")
        return False


# ============================================================
# 5. MAIN RUNNER
# ============================================================

def run():
    log.info("=" * 50)
    log.info("LinkedIn Auto-Email System — Starting run")

    sent_log = load_sent_log()
    new_sends = 0

    # --- Source 1: Gmail IMAP (LinkedIn notifications) ---
    applicants = fetch_linkedin_applicants()

    # --- Source 2: Manual CSV (fallback) ---
    csv_applicants = load_manual_applicants()
    applicants.extend(csv_applicants)

    if not applicants:
        log.info("No new applicants found.")
        return

    for applicant in applicants:
        email_addr = applicant.get("email", "").lower().strip()
        if not email_addr:
            continue

        # Skip if already emailed
        if email_addr in sent_log:
            log.info(f"Skipping {email_addr} — already emailed.")
            continue

        # Send email
        success = send_email(applicant)
        if success:
            sent_log[email_addr] = {
                "name": applicant["name"],
                "job": applicant["job"],
                "sent_at": datetime.now().isoformat(),
                "source": applicant.get("source", "unknown")
            }
            new_sends += 1

    save_sent_log(sent_log)
    log.info(f"Run complete. Sent {new_sends} new emails. Total logged: {len(sent_log)}")


if __name__ == "__main__":
    run()
