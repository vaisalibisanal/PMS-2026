# PHP Login & Registration Demo

This is a beginner-friendly PHP login and registration system using core PHP, MySQL (PDO), sessions, cookies, prepared statements, and secure password hashing.

## Files:
- config.php - DB connection, session start, helper functions
- register.php - Registration form and new user creation
- login.php - Login form and user authentication
- dashboard.php - Protected page for logged-in users
- logout.php - Session and cookie destruction
- style.css - Basic styling
- database.sql - Database and table creation
- index.php - Entry page with navigation links

## Database connection:
- DB name: `student_app`
- DB user: `test`
- DB pass: `test`

## SQL: 
See `database.sql` to create the database and `users` table.

## Quick setup (Linux/WSL):
1. Place files in `/var/www/html/MiniProject/` (already done).
2. Import SQL: `mysql -u test -p < database.sql` (use correct privileges).
3. Ensure Apache/Nginx + PHP are running and that document root serves the folder.
4. Visit `http://localhost/MiniProject/register.php` to create an account.

## Notes:
All PHP files include inline beginner-friendly comments explaining every line.
Built-in security features: prepared statements, password hashing, input validation, HttpOnly cookies.
This is very light weight web application.
