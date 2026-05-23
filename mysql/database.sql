-- database.sql - SQL statements to create database and users table
-- Create database (run as a user with privileges)
CREATE DATABASE IF NOT EXISTS student_app CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE student_app;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

-- Example: insert a test user (password: secret123)
-- INSERT INTO users (name, email, password) VALUES ('Test User', 'test@example.com', '<hashed_password_here>');
