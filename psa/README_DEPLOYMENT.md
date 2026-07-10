# PSA QR System Deployment Guide

This package is prepared for standard PHP/MySQL hosting.

## Upload contents

Upload the entire folder contents to your hosting public root, for example:
- public_html/psa/
- htdocs/psa/

Required files:
- PSA_HR_QR_Document_Tracking_System_Fixed.html
- index.html
- db_connection.php
- psa_schema.sql
- api/auth.php
- api/users.php
- api/documents.php
- api/db.php
- vercel.json

## Database setup

1. Create a MySQL database named psa_qr.
2. Import psa_schema.sql.
3. Update db_connection.php with your database credentials.

## Runtime requirements

- PHP 8+
- MySQL 5.7+ or MariaDB
- Apache or Nginx

## Test URLs

- https://yourdomain.com/psa/
- https://yourdomain.com/psa/api/db.php
