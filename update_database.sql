USE `lost_&_found`;

DROP TABLE IF EXISTS lost_items;
DROP TABLE IF EXISTS found_items;
DROP TABLE IF EXISTS claimed_items;

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

CREATE TABLE IF NOT EXISTS claimed_items (
    claim_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    location_found VARCHAR(255) NOT NULL,
    date_found DATE NOT NULL,
    claimed_by VARCHAR(50) NOT NULL,
    claim_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

