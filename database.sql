-- ============================================================
-- QueueLess — Smart Appointment & Virtual Queue Management System
-- Municipal Health Office of Naguilian, La Union
-- Import this file via phpMyAdmin (XAMPP)
-- ============================================================
CREATE DATABASE IF NOT EXISTS queueless CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE queueless;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('client','staff','admin') NOT NULL DEFAULT 'client',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  description TEXT,
  avg_minutes INT NOT NULL DEFAULT 10
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS queue_tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  queue_number VARCHAR(8) NOT NULL,
  user_id INT NOT NULL,
  service_id INT NOT NULL,
  type ENUM('appt','walkin','priority') NOT NULL DEFAULT 'walkin',
  slot_date DATE NULL,
  slot_time VARCHAR(10) NULL,
  notes VARCHAR(255) NULL,
  status ENUM('waiting','called','served','no-show','cancelled') NOT NULL DEFAULT 'waiting',
  booked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  called_at DATETIME NULL,
  served_at DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (service_id) REFERENCES services(id),
  INDEX idx_status (status, booked_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(120) NOT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  message VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS counters (
  day DATE NOT NULL,
  prefix CHAR(1) NOT NULL,
  value INT NOT NULL DEFAULT 0,
  PRIMARY KEY (day, prefix)
) ENGINE=InnoDB;

-- ---------- SEED DATA ----------
INSERT INTO services (name, description, avg_minutes) VALUES
('General Consultation','Checkups, diagnosis & treatment by the municipal physician.',10),
('Vaccination / Immunization','Routine immunization for children, adults & seniors.',8),
('Dental Services','Oral examination, tooth extraction & dental consultation.',15),
('Laboratory Services','Blood tests, urine tests & basic diagnostic procedures.',12),
('Medical Certificate','Issuance of medical certificates & health clearances.',5),
('Prenatal / Maternal Care','Prenatal checkups & maternal health services.',14);

-- Pre-created accounts (passwords hashed with PHP password_hash / bcrypt):
--   Staff:   staff@mho-naguilian.gov.ph  /  staff123
--   Admin:   admin@mho-naguilian.gov.ph  /  admin123
--   Patient: patient@email.com           /  patient123
INSERT INTO users (name, email, password, role) VALUES
('Dr. Staff Demo',       'staff@mho-naguilian.gov.ph', '$2y$10$oWCLgOnbRiY5B9imDRCiCuD/ayxYy7.JV.QGfOHe58E5FG/GXBsWm', 'staff'),
('System Administrator', 'admin@mho-naguilian.gov.ph', '$2y$10$5KK4wc.yONcb0.SNI/sbvumOxb5KV/Qjnux620bGME0J73bs2zO9W', 'staff'),
('Admin User',           'admin@queueless.com',        '$2y$10$5KK4wc.yONcb0.SNI/sbvumOxb5KV/Qjnux620bGME0J73bs2zO9W', 'staff'),
('Juan M. Dela Cruz',    'patient@email.com',          '$2y$10$vrtFzNKsAUtMgn8Plrq5o.rqETSTA3BkoN9M/lUVgCre2qo/OlYpq', 'client')
ON DUPLICATE KEY UPDATE password = VALUES(password), role = VALUES(role);

-- The queue starts empty each day; clients generate numbers on first use.

INSERT INTO counters (day, prefix, value) VALUES
(CURDATE(), 'W', 0), (CURDATE(), 'A', 0), (CURDATE(), 'P', 0);

INSERT INTO announcements (message) VALUES
('Welcome to the Municipal Health Office of Naguilian. Please secure your queue number and wait for your turn. Have your documents ready. Thank you!');
