-- PSA QR Document Tracking System SQL schema
-- Import this file in phpMyAdmin to create the database and tables.

CREATE DATABASE IF NOT EXISTS psa_qr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE psa_qr;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100) DEFAULT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','personnel','employee') NOT NULL DEFAULT 'employee',
  active TINYINT(1) NOT NULL DEFAULT 1,
  no_middle_name TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  qr_key VARCHAR(255) NOT NULL UNIQUE,
  control_number VARCHAR(50) NOT NULL UNIQUE,
  document_type VARCHAR(100) NOT NULL,
  title VARCHAR(255) NOT NULL,
  employee VARCHAR(150) DEFAULT NULL,
  date_submitted DATE DEFAULT NULL,
  origin VARCHAR(150) NOT NULL,
  current_office VARCHAR(150) NOT NULL,
  destination_office VARCHAR(150) DEFAULT NULL,
  priority VARCHAR(50) DEFAULT 'Normal',
  status VARCHAR(50) DEFAULT 'Received',
  assigned_to INT UNSIGNED DEFAULT NULL,
  created_by INT UNSIGNED DEFAULT NULL,
  description TEXT DEFAULT NULL,
  history TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL,
  FOREIGN KEY (assigned_to) REFERENCES users(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
