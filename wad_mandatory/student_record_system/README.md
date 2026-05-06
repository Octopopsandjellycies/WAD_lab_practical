# Student Record System
### Full-Stack PHP + MySQL Admin Portal

---

## Quick Setup

### Prerequisites
- PHP 7.4+ with the `mysqli` extension enabled
- MySQL 5.7+ or MariaDB 10+
- A local server: XAMPP, WAMP, MAMP, or Laragon

---

## Installation

### 1. Place the files
Copy the entire project folder into your web server root:

| Server | Path |
|--------|------|
| XAMPP (Windows) | `C:\xampp\htdocs\student_record_system\` |
| WAMP (Windows) | `C:\wamp64\www\student_record_system\` |
| MAMP (Mac) | `/Applications/MAMP/htdocs/student_record_system/` |
| Linux Apache | `/var/www/html/student_record_system/` |

### 2. Create the database
Open phpMyAdmin at `http://localhost/phpmyadmin`, then:

1. Click **New** in the left sidebar
2. Name it `student_db` and click **Create**
3. Click the `student_db` database, then the **Import** tab
4. Click **Choose File**, select `database.sql`, then click **Go**

Or run it via MySQL CLI:
```bash
mysql -u root -p < database.sql
```

This creates the `students`, `courses`, and `admin_users` tables and seeds default data.

### 3. Configure the database connection
Open `config.php` and update if your MySQL credentials differ:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // your MySQL username
define('DB_PASS', '');       // your MySQL password (blank by default in XAMPP)
define('DB_NAME', 'student_db');
```

### 4. Ensure the uploads folder is writable
```bash
# Linux / Mac
chmod 755 uploads/
```
On Windows, right-click the `uploads/` folder → Properties → Security → enable Write permission.

### 5. Open the app
```
http://localhost/student_record_system/login.php
```

---

## Default Login

| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `password` |

To change the password, generate a new bcrypt hash and update the `admin_users` table:
```php
// Run this once in a temporary PHP file, then delete it
echo password_hash('YourNewPassword', PASSWORD_DEFAULT);
```
Then paste the output into phpMyAdmin → `admin_users` → Edit → `password` field.

---

## File Structure

```
student_record_system/
├── config.php              Database connection and courses table bootstrap
├── session_bootstrap.php   Secure session configuration (lifetime, SameSite, etc.)
├── auth_check.php          Session guard — included at the top of every protected page
├── login.php               Admin login form
├── logout.php              Destroys session and redirects to login
├── index.php               Dashboard — stats, recent students, quick actions
├── add_student.php         Add student form with client-side and server-side validation
├── edit_student.php        Pre-filled edit form; replaces photo on upload
├── view_students.php       AJAX student table with search, sort, and delete
├── manage_courses.php      Create and delete course options
├── fetch_students.php      AJAX endpoint — returns all students as JSON
├── search_students.php     AJAX endpoint — returns filtered students as JSON
├── delete_student.php      AJAX endpoint — deletes a student and their photo
├── style.css               All custom styles (light theme, cards, table, forms)
├── database.sql            Full database schema and seed data
└── uploads/                Uploaded student photos (auto-created if missing)
```

---

## Features

### Authentication
- `password_verify()` with bcrypt hashing
- Session ID regenerated on login and every 30 minutes
- Secure cookie flags: `HttpOnly`, `SameSite=Lax`
- Every protected page guarded by `auth_check.php`

### Dashboard
- Bootstrap 5 responsive navbar
- Stat cards: total students, courses offered, latest enrollment
- Quick action links and a recent-students preview table

### Add Student
- Dropdown populated from the `courses` table (managed separately)
- Client-side validation before submission — no `alert()`:
  - Name: letters only, minimum 3 characters (regex)
  - Email: valid format (regex)
  - Phone: exactly 10 digits (regex)
  - DOB: must be a past date
  - Photo: `.jpg` or `.png` only
- Inline green tick / red message per field via DOM manipulation
- Drag-and-drop photo upload with live preview
- Server-side validation with prepared statements

### View Students
- Table populated via AJAX on page load (`fetch_students.php`)
- Live search on every keyup, debounced 250ms (`search_students.php`)
- Column sorting on Name and Course using JavaScript array sort
- AJAX delete with a Bootstrap modal confirmation dialog
- Row removed with `fadeOut()` — no page refresh
- Rows inserted with `fadeIn()` effect

### Edit Student
- Form pre-filled from the database by student ID
- Same validation rules as Add Student
- Old photo file deleted automatically when a new one is uploaded

### Manage Courses
- Create and delete course options from a dedicated page
- Courses assigned to students are protected from deletion
- Student count per course shown in the table

---

## Troubleshooting

**"Invalid username or password" on first login**
Run this in phpMyAdmin → `student_db` → SQL tab:
```sql
UPDATE admin_users
SET password = '$2y$10$lYh7xf3RcH2TP8/kBpbW/OOpq0gC4MORZKcfHFOC0AKmanvxuBbDe'
WHERE username = 'admin';
```
Then log in with `admin` / `password`.

**"Not Found" error**
The PHP files must sit directly inside `htdocs/student_record_system/`, not in a nested subfolder. Check that `login.php` exists at `C:\xampp\htdocs\student_record_system\login.php`.

**Database connection error**
Confirm Apache and MySQL are both running (green) in the XAMPP Control Panel. Check that `DB_PASS` in `config.php` matches your MySQL root password (blank by default in XAMPP).

**Photos not uploading**
Verify the `uploads/` folder exists and is writable. On Windows, disable read-only on the folder. On Linux/Mac, run `chmod 755 uploads/`.

**Apache won't start (port conflict)**
Another program (Skype, IIS, or another web server) is using port 80. In XAMPP → Apache → Config, change `Listen 80` to `Listen 8080`. Access the app at `http://localhost:8080/student_record_system/login.php`.

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8+, MySQL via `mysqli` |
| Frontend | Bootstrap 5.3, jQuery 3.7, Bootstrap Icons |
| Fonts | Plus Jakarta Sans, Cormorant Garamond (Google Fonts) |
| Security | bcrypt passwords, prepared statements, session hardening |
