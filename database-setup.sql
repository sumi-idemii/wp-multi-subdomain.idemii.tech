-- WordPress Multisite Database Setup Script
-- Run this script as MySQL root user or a user with CREATE DATABASE privileges
-- 
-- Usage:
--   mysql -u root -p < database-setup.sql
--   or
--   mysql -u root -p
--   source database-setup.sql;

-- Create database
CREATE DATABASE IF NOT EXISTS wordpress_multisite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (change 'your_secure_password' to a strong password)
CREATE USER IF NOT EXISTS 'wp_user'@'localhost' IDENTIFIED BY 'your_secure_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON wordpress_multisite.* TO 'wp_user'@'localhost';

-- For remote connections (if needed for AWS Lightsail)
-- CREATE USER IF NOT EXISTS 'wp_user'@'%' IDENTIFIED BY 'your_secure_password';
-- GRANT ALL PRIVILEGES ON wordpress_multisite.* TO 'wp_user'@'%';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Show created database
SHOW DATABASES LIKE 'wordpress_multisite';

-- Show user privileges
SHOW GRANTS FOR 'wp_user'@'localhost';

