<?php
/**
 * PSA-HR QR Document Tracking System
 * Database connection helper for WAMP / phpMyAdmin.
 *
 * Place this file in your project root and include it in your PHP scripts.
 * Ensure WAMP is running and phpMyAdmin is available at http://localhost/phpmyadmin.
 */

// Database configuration
// Update these values for your hosting provider.
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'psa_qr');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');

define('DB_DSN', 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4');

/**
 * Create and return a PDO database connection.
 *
 * @return PDO
 */
function getDbConnection()
{
    try {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO(DB_DSN, DB_USER, DB_PASSWORD, $options);
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

/**
 * Example: fetch users from the database.
 */
function fetchUsers()
{
    $pdo = getDbConnection();
    $stmt = $pdo->query('SELECT id, first_name, middle_name, last_name, email, username, role, active, created_at FROM users ORDER BY created_at DESC');
    return $stmt->fetchAll();
}

/**
 * Example: insert a new user.
 */
function insertUser($firstName, $middleName, $lastName, $email, $username, $hashedPassword, $role)
{
    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO users (first_name, middle_name, last_name, email, username, password, role, active, created_at)
         VALUES (:first_name, :middle_name, :last_name, :email, :username, :password, :role, 1, NOW())'
    );
    $stmt->execute([
        ':first_name' => $firstName,
        ':middle_name' => $middleName,
        ':last_name' => $lastName,
        ':email' => $email,
        ':username' => $username,
        ':password' => $hashedPassword,
        ':role' => $role,
    ]);
    return $pdo->lastInsertId();
}

/**
 * Example: reset a user password.
 */
function resetUserPassword($userId, $hashedPassword)
{
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id');
    return $stmt->execute([
        ':password' => $hashedPassword,
        ':id' => $userId,
    ]);
}

/**
 * Example SQL schema for the PSA-HR system.
 *
 * CREATE DATABASE psa_hr_qr_system;
 * USE psa_hr_qr_system;
 *
 * CREATE TABLE users (
 *   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 *   first_name VARCHAR(100) NOT NULL,
 *   middle_name VARCHAR(100) DEFAULT NULL,
 *   last_name VARCHAR(100) NOT NULL,
 *   email VARCHAR(150) NOT NULL UNIQUE,
 *   username VARCHAR(100) NOT NULL UNIQUE,
 *   password VARCHAR(255) NOT NULL,
 *   role ENUM('admin','personnel','employee') NOT NULL DEFAULT 'employee',
 *   active TINYINT(1) NOT NULL DEFAULT 1,
 *   no_middle_name TINYINT(1) NOT NULL DEFAULT 0,
 *   created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 *   updated_at DATETIME NULL DEFAULT NULL
 * );
 *
 * CREATE TABLE documents (
 *   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 *   control_number VARCHAR(50) NOT NULL UNIQUE,
 *   qr_key VARCHAR(255) NOT NULL UNIQUE,
 *   type VARCHAR(100) NOT NULL,
 *   title VARCHAR(255) NOT NULL,
 *   employee VARCHAR(150) DEFAULT NULL,
 *   date_submitted DATE DEFAULT NULL,
 *   origin VARCHAR(150) NOT NULL,
 *   current_office VARCHAR(150) NOT NULL,
 *   destination_office VARCHAR(150) DEFAULT NULL,
 *   priority VARCHAR(50) DEFAULT 'Normal',
 *   status VARCHAR(50) DEFAULT 'Received',
 *   assigned_to INT UNSIGNED DEFAULT NULL,
 *   created_by INT UNSIGNED DEFAULT NULL,
 *   description TEXT DEFAULT NULL,
 *   history TEXT DEFAULT NULL,
 *   created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 *   updated_at DATETIME NULL DEFAULT NULL,
 *   FOREIGN KEY (assigned_to) REFERENCES users(id),
 *   FOREIGN KEY (created_by) REFERENCES users(id)
 * );
 *
 * Note: Adjust the schema for your document workflow and additional tables.
 */

// Usage example:
// require_once __DIR__ . '/db_connection.php';
// $users = fetchUsers();
// var_dump($users);
