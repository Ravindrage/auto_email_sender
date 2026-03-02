# LinkedIn Auto-Email System — Setup Guide
## PythonAnywhere Deployment

---

## 📁 Files in This Project
```
linkedin_auto_email/
├── config.py          ← Edit YOUR settings here
├── main.py            ← Main automation script
├── applicants.csv     ← Manual fallback: add applicants here
├── sent_emails.json   ← Auto-created: tracks who got emailed
└── auto_email.log     ← Auto-created: activity log
```

---

## ⚙️ STEP 1 — Enable Gmail App Password

LinkedIn notifications come to your Gmail. We read them via IMAP.

1. Go to: https://myaccount.google.com/security
2. Enable **2-Step Verification** (required)
3. Go to: https://myaccount.google.com/apppasswords
4. Create App Password → Select "Mail" → Copy the 16-char password
5. Paste it in `config.py` → `EMAIL_PASSWORD`

Also enable IMAP in Gmail:
- Gmail Settings → See all settings → Forwarding and POP/IMAP → Enable IMAP

---

## ⚙️ STEP 2 — Edit config.py

Open `config.py` and fill in:
- `EMAIL_ADDRESS` — your Gmail
- `EMAIL_PASSWORD` — App Password from Step 1
- `COMPANY_NAME` — your company
- `JOB_TITLE` — job you posted
- `EMAIL_BODY` — your custom message with your links

---

## ⚙️ STEP 3 — Upload to PythonAnywhere

1. Log in to https://www.pythonanywhere.com
2. Go to **Files** tab
3. Create folder: `/home/yourusername/linkedin_auto_email/`
4. Upload all files into that folder

---

## ⚙️ STEP 4 — Install Dependencies

In PythonAnywhere → **Bash console**:
```bash
pip install --user imapclient
```

---

## ⚙️ STEP 5 — Test It Manually

In PythonAnywhere Bash console:
```bash
cd ~/linkedin_auto_email
python main.py
```

Check `auto_email.log` to see what happened.

---

## ⚙️ STEP 6 — Schedule Automatic Runs

1. Go to PythonAnywhere → **Tasks** tab
2. Click **Add a new scheduled task**
3. Set command: `python /home/yourusername/linkedin_auto_email/main.py`
4. Set schedule: **Every hour** (free plan minimum)
5. Save

Now it runs automatically every hour! ✅

---

## 📬 TWO WAYS TO GET APPLICANT EMAILS

### Method A: Automatic (Gmail IMAP) — Recommended
- LinkedIn sends you a notification email when someone applies
- This script reads those emails and extracts applicant info
- Works automatically, no manual work needed

### Method B: Manual CSV (Fallback)
- Go to LinkedIn → Jobs → Your Job Post → Applicants
- Export or manually copy applicant emails into `applicants.csv`
- Run the script — it will email everyone in the CSV
- Already-emailed people are skipped (tracked in sent_emails.json)

---

## 📋 Troubleshooting

| Issue | Fix |
|-------|-----|
| "Authentication failed" | Use App Password, not real Gmail password |
| "IMAP disabled" | Enable IMAP in Gmail settings |
| No applicants found | Use CSV method as fallback |
| Emails going to spam | Add SPF/DKIM or use SendGrid instead of Gmail |

---

## 🚀 Upgrade: Use SendGrid for Better Delivery

Replace Gmail SMTP with SendGrid (free 100 emails/day):
```python
# In main.py, replace send_email() SMTP section with:
import sendgrid
from sendgrid.helpers.mail import Mail

sg = sendgrid.SendGridAPIClient(api_key='YOUR_SENDGRID_API_KEY')
message = Mail(from_email=EMAIL_ADDRESS, to_emails=to,
               subject=subject, plain_text_content=body)
sg.send(message)
```
