USE `lost_&_found`;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS lost_items;
DROP TABLE IF EXISTS found_items;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS claimed_items;

-- Create lost_items table with improved structure
CREATE TABLE lost_items (
    lost_id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id VARCHAR(50) NOT NULL,
    reporter_type ENUM('student', 'staff') NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    date_lost DATE NOT NULL,
    color VARCHAR(50) NOT NULL,
    estimated_time VARCHAR(50) NOT NULL,
    location_lost VARCHAR(100) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'lost',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create found_items table with similar structure
CREATE TABLE found_items (
    found_id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id VARCHAR(50) NOT NULL,
    reporter_type ENUM('student', 'staff') NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    date_found DATE NOT NULL,
    color VARCHAR(50) NOT NULL,
    estimated_time VARCHAR(50) NOT NULL,
    location_found VARCHAR(100) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'found',
    claimed_by VARCHAR(50) DEFAULT NULL,
    claimed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add claim-related columns to found_items table if they don't exist
ALTER TABLE found_items
ADD COLUMN IF NOT EXISTS claimed_by VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS claimed_at DATETIME NULL,
ADD COLUMN IF NOT EXISTS claim_status ENUM('pending', 'approved', 'rejected') NULL;

-- Add or update indexes for better performance
ALTER TABLE found_items
ADD INDEX IF NOT EXISTS idx_claimed_by (claimed_by),
ADD INDEX IF NOT EXISTS idx_claim_status (claim_status),
ADD INDEX IF NOT EXISTS idx_reporter_type_id (reporter_type, reporter_id);

ALTER TABLE lost_items
ADD INDEX IF NOT EXISTS idx_reporter_type_id (reporter_type, reporter_id);

-- Create admin table

-- Add index for admin table
ALTER TABLE admins
ADD INDEX idx_username (username),
ADD INDEX idx_email (email),
ADD INDEX idx_role (role);

-- Insert initial admin account (password will be: Admin@123)

-- Create claimed_items table
CREATE TABLE IF NOT EXISTS claimed_items (
    claim_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    location_found VARCHAR(255) NOT NULL,
    date_found DATE NOT NULL,
    claimed_by VARCHAR(50) NOT NULL,
    claim_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add index for claimed items
ALTER TABLE claimed_items
ADD INDEX idx_claimed_by (claimed_by),
ADD INDEX idx_claimed_at (claimed_at); 