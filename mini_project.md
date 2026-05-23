# MiniProject — End-to-End Workflow

## Overview
This document describes the end-to-end workflow for the MiniProject PHP lication located at the repository root. It covers prerequisites, setup, configuration, database initialization, running locally, testing flows, deployment notes, security considerations, and troubleshooting.

## Prerequisites
- PHP 7.4+ (or PHP 8.x)
- MySQL or MariaDB
- Composer (optional, not required for this simple app)
- Access to the project folder (this repository)

## Files (what each file is for)
- `config.php` — database connection and app configuration.
- `Constants.php` — application constants used across files.
- `index.php` — public landing / home page.
- `register.php` — user registration form and processing.
- `login.php` — user login form and processing.
- `dashboard.php` — protected user dashboard (current file).
- `logout.php` — logout handler.
- `style.css` — front-end styles.
- `database.sql` — SQL schema and seed data.
- `README.md` — repository overview.

## Setup & Installation
1. Clone or copy the project into your web root. In this environment the project lives at the workspace root.

2. Create a database and import the provided schema:

```bash
# create database (example name: miniproject)
mysql -u root -p -e "CREATE DATABASE miniproject CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
# import schema
mysql -u root -p miniproject < database.sql
```

3. Configure database credentials in `config.php` (set DB host, database, user, password). If `Constants.php` holds config values, update those instead.

4. Ensure PHP can write sessions (default) and that file permissions allow the webserver to read project files.

## Running Locally (development)
You can run the app using PHP's built-in server from the project root.

```bash
cd /path/to/MiniProject
php -S 0.0.0.0:8000
```

Then open http://localhost:8000 (or use your WSL host forwarding) in a browser.

If using Apache/Nginx, point your virtual host's document root to the project folder.

## Typical User Flows to Test
- Register a new user: open `register.php`, fill form, submit — then verify a new DB record in the `users` table.
- Login: open `login.php`, log in with registered credentials — should redirect to `dashboard.php` and set session.
- Dashboard: verify protected content only appears when logged in.
- Logout: run `logout.php` and confirm session is cleared and access to `dashboard.php` is blocked.

## Testing & Validation
- Manual: follow the user flows above and inspect database rows.
- Quick SQL check:

```sql
SELECT id, email, created_at FROM users ORDER BY id DESC LIMIT 5;
```

- If password hashing is used, confirm `password_hash()` was used and `password_verify()` is used for login.

## Security Considerations
- Passwords: use `password_hash()` for storing and `password_verify()` when checking.
- Prepared statements / parameterized queries: avoid direct string interpolation into SQL.
- Sessions: call `session_start()` at top of pages that use sessions; regenerate session IDs after login.
- Input validation & output escaping: validate server-side and escape output to prevent XSS.
- Config secrets: do not commit database passwords to the repository. Prefer environment variables or a separate non-tracked config file.
- Use HTTPS in production.

## Deployment Notes
- Use a production-ready web server (Apache, Nginx) and PHP-FPM.
- Set correct file permissions: web server should not have write access to PHP sources.
- Move sensitive configuration outside webroot or protect it via server rules.
- Set `display_errors = Off` in production and log errors to a secure file.
- Backup the database regularly and version any migration scripts.

## Troubleshooting
- Blank pages: enable error logging or temporarily set `display_errors = On` in `php.ini` for debugging.
- Database connection errors: verify credentials in `config.php` and that the DB server is running and accessible.
- Session issues: check `session.save_path` permission and PHP session settings.
- Port conflicts when using `php -S`: pick another port (e.g., `:8080`) or stop the conflicting service.

## Maintenance & Next Steps
- Add CSRF tokens to all state-changing forms.
- Add automated tests (PHPUnit) for critical functions.
- Introduce a deployment script or CI pipeline to automate deploys and DB migrations.

## Contributor Notes
- Follow simple PHP procedural structure in existing files.
- Keep UI minimal; update `style.css` for visual changes.
- When editing `config.php` or `Constants.php`, ensure sensitive values are not committed.

---

Document created for the MiniProject repository. Update this file when you add features, change DB schema, or modify authentication flows.
