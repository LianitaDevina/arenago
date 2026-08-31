-- Tabel Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin_lapangan', 'user') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Venues 
CREATE TABLE venues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, 
    name VARCHAR(100) NOT NULL,
    location TEXT NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    rating DECIMAL(2,1) DEFAULT 0.0,
    description TEXT DEFAULT NULL,
    status ENUM('pending', 'approved') DEFAULT 'pending',
    image VARCHAR(255) DEFAULT 'default.jpg',
    facilities VARCHAR(255) DEFAULT '',
    latitude DECIMAL(10, 8) DEFAULT NULL,
    longitude DECIMAL(11, 8) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Courts 
CREATE TABLE courts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venue_id INT NOT NULL,
    court_name VARCHAR(50) NOT NULL,
    price_per_hour DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE
);

-- Insert Superadmin Default
INSERT INTO users (name, email, password, role) 
VALUES ('Super Admin', 'super@arenago.com', 'password123', 'superadmin');
