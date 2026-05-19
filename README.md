# SAMS – Staff Administration & Management System

A full-featured corporate staff management portal built with **PHP**, **MySQL**, and **Tailwind CSS**. SAMS provides role-based access control, an interactive org chart, project tracking, employee management, analytics, and a secure authentication system.

---

## ✨ Features

- 🔐 **Secure Authentication** — Session-based login with JWT support, bcrypt password hashing, and 2FA PIN
- 👥 **Employee Management** — Add, view, search, and manage employees with profile pictures
- 🌳 **Interactive Org Chart** — Drag-and-drop hierarchy management with CEO as the root node
- 📁 **Project Tracking** — Create, assign, and track project progress with role-based permissions
- 📊 **Analytics Dashboard** — Visual insights into employee distribution and project metrics
- 🔔 **Notifications** — Real-time notification panel for system events
- 🛡️ **Role & Permission System** — Granular per-role permission management
- 🌗 **Dark / Light Mode** — Persistent theme preference stored in localStorage
- ⚙️ **User Settings** — Profile updates, security settings, and compact view toggle

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Frontend | PHP (templated), Tailwind CSS, Vanilla JS |
| Backend | PHP 8+ |
| Database | MySQL (via PDO) |
| Auth | Session-based + JWT (`firebase/php-jwt`) |
| Dependencies | Composer |

---

## 📁 Project Structure

```
SAMS-Project/
├── index.php                   # Entry point (redirects to login/dashboard)
├── composer.json               # PHP dependencies
├── vendor/                     # Composer packages
│
├── Frontend/
│   ├── login.php               # Login page
│   ├── register.php            # Registration page
│   ├── forgot-password.php     # Password reset page
│   ├── dashboard/              # Main dashboard
│   ├── Employees/              # Employee management
│   ├── Projects/               # Project tracking
│   ├── Analytics/              # Analytics & charts
│   ├── Roles/                  # Role & permission management
│   ├── Notifications/          # Notification center
│   ├── Settings/               # User settings
│   ├── Support/                # Help & support
│   ├── auth/                   # Auth guard helpers
│   ├── shared/                 # Shared UI components (sidebar, header)
│   └── assets/                 # Images, icons, uploads
│
└── Backend/
    ├── api/                    # REST API endpoints (29 endpoints)
    ├── classes/
    │   └── User.php            # User model (auth, JWT, sessions)
    ├── config/
    │   ├── db.php              # PDO database connection
    │   ├── db_simple.php       # Simplified DB connection
    │   ├── encryption.php      # Encryption helpers
    │   └── jwt_config.php      # JWT secret & settings
    ├── database/
    │   ├── master_schema.sql   # Full database schema
    │   ├── setup_database.php  # Database initializer
    │   ├── init_db.php         # Table creation script
    │   └── seed_org_chart.php  # Sample org chart data seeder
    ├── dashboard/              # Dashboard-specific backend logic
    ├── helpers/                # Utility helpers
    └── tests/                  # Backend tests
```

---

## ⚙️ Setup & Installation

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (PHP 8.0+, MySQL, Apache)
- [Composer](https://getcomposer.org/)

### Steps

**1. Clone / copy the project**
```bash
# Place the project folder inside XAMPP's htdocs directory
C:\xampp\htdocs\SAMS-Project - 05\
```

**2. Install PHP dependencies**
```bash
cd "C:\xampp\htdocs\SAMS-Project - 05"
composer install
```

**3. Create the database**

Open **phpMyAdmin** → create a new database named `sams_db`, then run the schema:
```bash
# Via phpMyAdmin: import Backend/database/master_schema.sql
# Or via PHP CLI:
php Backend/database/setup_database.php
```

**4. Configure the database connection**

Edit `Backend/config/db.php` and set your credentials:
```php
$host = 'localhost';
$dbname = 'sams_db';
$username = 'root';
$password = '';
```

**5. Configure the JWT secret**

Edit `Backend/config/jwt_config.php` and replace the placeholder secret:
```php
define("JWT_SECRET", "your-actual-secret-key-here");
```

**6. Seed sample data (optional)**
```bash
php Backend/database/seed_org_chart.php
```

**7. Start XAMPP** (Apache + MySQL) and visit:
```
http://localhost/SAMS-Project%20-%2005/
```

---

## 🔑 Default Access

Register a new account at `/Frontend/register.php`. The first user can be promoted to CEO role via **phpMyAdmin** by setting `role_id` in the `users` table.

---

## 🔒 Security

- Passwords hashed with **bcrypt** (`PASSWORD_DEFAULT`)
- JWT tokens for stateless API authentication
- 2FA PIN support for sensitive operations
- Role-based access control on all API endpoints
- PDO prepared statements throughout (SQL injection safe)

---

## 📄 License

This project is developed for academic purposes as part of a college submission.
