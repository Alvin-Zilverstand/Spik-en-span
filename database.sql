-- Create the database
CREATE DATABASE IF NOT EXISTS spik_en_span;
USE spik_en_span;

-- Table for storing ticket information
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    category ENUM('adult', 'child', 'group') NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for storing employee login credentials
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- Store hashed passwords
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for storing scanned ticket logs
CREATE TABLE scanned_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(255) NOT NULL,
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_valid BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE (ticket_id)
);

-- Table for storing unique strings for users
CREATE TABLE unique_strings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    unique_code CHAR(6) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- Trigger to automatically generate a unique 6-character alphanumeric string for each user
DELIMITER $$
CREATE TRIGGER generate_unique_code
AFTER INSERT ON employees
FOR EACH ROW
BEGIN
    DECLARE unique_code CHAR(6);
    SET unique_code = (SELECT SUBSTRING(CONCAT(MD5(RAND()), MD5(RAND())), 1, 6));
    INSERT INTO unique_strings (user_id, unique_code) VALUES (NEW.id, unique_code);
END$$
DELIMITER ;

-- Insert a default employee account (username: admin, password: password)
INSERT INTO employees (username, password_hash)
VALUES ('admin', SHA2('password', 256)); -- Replace with a secure password hashing method
