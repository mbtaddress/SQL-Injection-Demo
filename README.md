# SQL Injection Demo Lab

An intentionally vulnerable PHP web application for learning and demonstrating SQL injection attacks — every attack type demonstrated side-by-side with its secure fix.

> ⚠️ **For educational use only.** Run this in an isolated local/Docker environment. Never expose it to a public network.

---

## Features

- **10 SQL injection attack types** demonstrated with live exploitable pages
- **Side-by-side vulnerable vs safe** implementations for every feature
- **Step-by-step walkthroughs** with payloads, Python scripts, and sqlmap commands
- **Vulnerable REST API** (4 endpoints with multiple injection vectors)
- **Database reset button** — restore default users instantly
- **phpMyAdmin** included for database inspection

---

## Attack Types Covered

| # | Attack | Difficulty |
|---|--------|-----------|
| 1 | Authentication Bypass | Basic |
| 2 | UNION-Based Data Extraction | Intermediate |
| 3 | Error-Based Injection | Intermediate |
| 4 | Blind Boolean-Based | Advanced |
| 5 | Time-Based Blind | Advanced |
| 6 | Second-Order (Stored) | Advanced |
| 7 | Privilege Escalation via UPDATE | Intermediate |
| 8 | WAF Bypass Techniques | Advanced |
| 9 | Out-of-Band (OOB) | Advanced |
| 10 | Routed SQL Injection | Advanced |
| 11 | API SQL Injection (REST) | Intermediate |

---

## Quick Start (Docker)

### Prerequisites
- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Run

```bash
git clone https://github.com/mbtaddress/SQL-Injection-Demo.git
cd sqlinjection-demo
docker compose up --build -d
```

### Access

| Service | URL |
|---------|-----|
| App (Home) | http://localhost:8080 |
| Vulnerable Lab | http://localhost:8080/login.php |
| Safe Lab | http://localhost:8080/loginsafe.php |
| Vulnerable API | http://localhost:8080/api/ |
| phpMyAdmin | http://localhost:8081 |
| Reset Database | http://localhost:8080/reset.php |

### Default Login Credentials

| Email | Password | Role |
|-------|----------|------|
| admin@demo.com | admin123 | admin |
| enish@gmail.com | 123456 | admin |
| alice@demo.com | alice123 | user |
| bob@demo.com | bob123 | user |

### phpMyAdmin
- **Server:** db
- **Username:** root
- **Password:** root

---

## Project Structure

```
sqlinjection-demo/
├── api/                    # Vulnerable REST API endpoints
│   ├── users.php           # GET: id, role, sort, search injection
│   ├── search.php          # GET: LIKE + LIMIT injection
│   ├── login.php           # POST: JSON body + header injection
│   └── user_update.php     # PUT: UPDATE + mass assignment injection
├── Config/
│   ├── Config.php          # DB credentials (reads from env)
│   ├── MySQLConnection.php # PDO connection class
│   └── sql_demo.sql        # Database schema + seed data
├── classes/
│   ├── Config.php          # Config class (Docker-aware)
│   └── MySQLConnection.php # PDO connection
├── pages/
│   ├── walkthroughs/       # Step-by-step attack guides
│   ├── union_search.php    # UNION injection demo
│   ├── error_based.php     # Error-based injection demo
│   ├── blind_boolean.php   # Blind boolean demo
│   ├── time_based.php      # Time-based blind demo
│   ├── second_order.php    # Second-order injection demo
│   ├── oob.php             # Out-of-Band injection demo
│   ├── routed.php          # Routed SQLi demo
│   ├── privesc.php         # Privilege escalation demo
│   └── waf_bypass.php      # WAF bypass demo
├── index.php               # Home page with vulnerability cards
├── login.php               # Vulnerable login
├── loginsafe.php           # Safe login
├── register.php            # Vulnerable registration
├── register_safe.php       # Safe registration
├── profile.php             # Vulnerable lab (all attack pages)
├── profile_safe.php        # Safe lab (all fixed pages)
├── reset.php               # Database reset + reseed
├── db_login.php            # Vulnerable login handler
├── db_login_safe.php       # Safe login handler (PDO)
├── Dockerfile
└── docker-compose.yml
```

---

## Running Without Docker

Requirements: PHP 8.1+, MySQL 8.0+, Composer

```bash
composer install
# Import Config/sql_demo.sql into your MySQL instance
# Update Config/Config.php and classes/Config.php with your DB credentials
# Serve with Apache or: php -S localhost:8080
```

---

## Tools for Practice

- **sqlmap** — `sqlmap -u "http://localhost:8080/api/users.php?id=1" --dbs --batch`
- **Burp Suite** — intercept and replay requests via proxy at `127.0.0.1:8080`
- **curl** — `curl "http://localhost:8080/api/users.php?id=1 AND SLEEP(3)"`
- **Python requests** — see walkthrough scripts in the app

---

## Stop / Reset

```bash
# Stop containers
docker compose down

# Stop and wipe the database volume (full reset)
docker compose down -v

# Restart fresh
docker compose up -d
```

---

## Disclaimer

This application contains **intentional security vulnerabilities**. It is designed exclusively for security education in controlled environments. Do not deploy on any public-facing server. The authors are not responsible for any misuse.
