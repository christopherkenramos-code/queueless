# QueueLess — Smart Appointment & Virtual Queue Management System
Municipal Health Office of Naguilian, La Union · Capstone Project (DMMMSU Mid-La Union Campus, BSIT)

## Stack (as described in the Methodology chapter)
- **Frontend:** HTML5, CSS3, JavaScript (fetch/AJAX polling for real-time updates)
- **Backend:** PHP 8 (PDO prepared statements — SQL-injection safe)
- **Database:** MySQL / MariaDB via phpMyAdmin
- **Server:** XAMPP (Apache + MySQL)

## Setup (5 minutes)
1. Install [XAMPP](https://www.apachefriends.org) and start **Apache** and **MySQL**.
2. Copy the `queueless` folder into `C:\xampp\htdocs\` (Windows) or `/opt/lampp/htdocs/` (Linux).
3. Open `http://localhost/phpmyadmin` → **Import** → choose `database.sql` → Go.
   (This creates the `queueless` database with tables, services, demo accounts and a small seed queue.)
4. If your MySQL root password is not empty, edit `config/database.php`.
5. Open `http://localhost/queueless/` 🌿

## Pre-created accounts (imported with database.sql)
| Role / Description | Email / Username | Password | Notes |
|---|---|---|---|
| Admin / Staff | `staff@mho-naguilian.gov.ph` or `staff` | `staff123` | Direct access to Staff/Admin Dashboard |
| Admin / Staff | `admin@mho-naguilian.gov.ph` or `admin` | `admin123` | Direct access to Staff/Admin Dashboard |
| Admin / Staff | `admin@queueless.com` | `admin123` | Direct access to Staff/Admin Dashboard |
| Patient / Client | `patient@email.com` or `patient` | `patient123` | Direct access to Patient Dashboard |
| Justin (Admin) | `justindometita@gmail.com` | `1234` | Direct access to Staff/Admin Dashboard |

*Note: On the login page, you can enter either the full email address or simply type `admin`, `staff`, or `patient`.*

Additional accounts: register at `register.php` (clients).
To promote any client to staff/admin, run in phpMyAdmin SQL:
`UPDATE users SET role = 'staff' WHERE email = 'their@email.com';`

## Feature map (Chapter 2 functional requirements)
- Secure registration & login — `login.php`, `register.php` (password_hash / password_verify)
- Online appointment scheduling — patient dashboard booking form
- Walk-in registration — "Get Walk-in Number Now" button
- Unique queue numbers — A-### (appointment), W-### (walk-in), P-### (priority), auto-reset daily via the `counters` table
- Estimated wait times — computed from per-service average durations (`services.avg_minutes`)
- Real-time queue monitoring — AJAX polling (4s patient/staff, 3s board)
- Automated notifications — `notifications` table + on-screen toast + beep alert when called
- Staff queue management — call next / mark served / no-show
- Live queue display board — `board.php` (full-screen, auto-refresh, announcements ticker)
- Secure data storage — MySQL via PDO prepared statements

## Recommendation features included (beyond the docx requirements)
1. **Priority lane (P-numbers)** — seniors, PWDs and pregnant clients are served first, sorted ahead of the regular queue.
2. **Beep + toast alert** — audio-visual alert when a client's number is called (patient dashboard and display board).
3. **One-active-ticket rule** — prevents queue abuse; a user must cancel before re-booking.
4. **Daily auto-reset** — stale waiting/called tickets from previous days are auto-archived as no-show when staff opens the dashboard.
5. **Staff daily report + CSV export** — `staff/export.php` downloads today's served transactions (useful as evidence for UAT / data analysis chapter).
6. **Board announcements** — staff can post announcements that scroll on the public display board.
7. **Data-privacy posture** — prepared statements everywhere, output escaping, session regeneration on login.

## Pages
- `/` landing page (2D leaf-canvas animation, live mini board, animated counters)
- `/board.php` public queue display (open on a TV/monitor at the health office)
- `/patient/dashboard.php` book / walk-in / track / notifications
- `/staff/dashboard.php` queue operations, stats, announcements, CSV export
