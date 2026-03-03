# LinkedIn Auto-Email System (PHP) — Railway Deployment

## 📁 Project Files
```
linkedin_auto_email/
├── main.php           ← Entry point — run this
├── config.php         ← All settings (reads Railway env vars)
├── mailer.php         ← Sends email via Gmail SMTP (PHPMailer)
├── imap_fetch.php     ← Reads LinkedIn notifications from Gmail
├── csv_fetch.php      ← CSV fallback loader
├── sent_log.php       ← Tracks who already got emailed
├── logger.php         ← Logging utility
├── composer.json      ← PHPMailer dependency
├── railway.json       ← Railway cron: runs every hour
├── nixpacks.toml      ← PHP 8.1 + IMAP extension config
├── applicants.csv     ← Manual fallback: add applicant emails here
└── data/              ← Auto-created: logs + sent_emails.json
```

---

## ⚙️ STEP 1 — Get Gmail App Password

1. Visit: https://myaccount.google.com/security
2. Enable **2-Step Verification**
3. Visit: https://myaccount.google.com/apppasswords
4. Create password → "Mail" → copy 16-char code
5. Enable IMAP: Gmail → Settings → Forwarding and POP/IMAP → Enable IMAP

---

## ⚙️ STEP 2 — Push to GitHub

```bash
git init
git add .
git commit -m "LinkedIn auto-email PHP system"
git remote add origin https://github.com/YOU/linkedin-auto-email-php.git
git push -u origin main
```

> ⚠️ Make sure `.gitignore` is included so `vendor/` and `data/` aren't committed.

---

## ⚙️ STEP 3 — Deploy on Railway

1. Go to https://railway.app → **New Project**
2. Click **Deploy from GitHub repo** → select your repo
3. Railway detects PHP via `nixpacks.toml` and installs Composer deps ✅

---

## ⚙️ STEP 4 — Set Environment Variables

In Railway → Your Service → **Variables** tab:

| Variable       | Value                            |
|----------------|----------------------------------|
| EMAIL_ADDRESS  | you@gmail.com                    |
| EMAIL_PASSWORD | xxxx xxxx xxxx xxxx (App PW)     |
| COMPANY_NAME   | Your Company Name                |
| JOB_TITLE      | Software Engineer                |

---

## ⚙️ STEP 5 — Cron is Auto-configured ✅

`railway.json` sets cron to run **every hour**:
```json
"cronSchedule": "0 * * * *"
```

Change to every 30 mins: `*/30 * * * *`

---

## 📬 Two Ways to Get Applicant Emails

### Method A — Automatic (Gmail IMAP)
LinkedIn emails you when someone applies:
> "John Smith applied to your job: Software Engineer"

The script reads those, extracts the applicant's email, and sends your custom reply automatically.

### Method B — Manual CSV
1. LinkedIn → Jobs → Your Post → Applicants → copy emails
2. Paste into `applicants.csv`
3. Push to GitHub → Railway redeploys → emails sent

---

## 📋 Troubleshooting

| Problem | Fix |
|---------|-----|
| IMAP connection fails | Use App Password + enable IMAP in Gmail |
| PHPMailer auth error | Double-check App Password in Railway Variables |
| No applicants found | Use CSV method as fallback |
| Emails go to spam | Switch to SendGrid free tier |

---

## 🔍 View Logs

Railway → Your service → **Deployments** → click run → see stdout logs in real time.

Local test:
```bash
composer install
php main.php
```
