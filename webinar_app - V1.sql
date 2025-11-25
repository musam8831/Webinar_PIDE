-- Database & tables
CREATE DATABASE IF NOT EXISTS webinar_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE webinar_app;

DROP TABLE IF EXISTS webinars;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE webinars (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,
  initiated_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_webinar_user FOREIGN KEY (initiated_by) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_time (start_at, end_at)
) ENGINE=InnoDB;

-- Default admin
INSERT INTO users (name,email,password_hash,role)
VALUES ('Administrator','admin@example.com','$2y$10$QWQ.iXbT1G4i6q5a8QTbiO6i1Xk9v1yU4rvh9B4m3k3l3v2P9sU9u','admin');
-- Password hash corresponds to: Admin@123  (bcrypt)

-- Sample user
INSERT INTO users (name,email,password_hash,role)
VALUES ('Alice','alice@example.com','$2y$10$QWQ.iXbT1G4i6q5a8QTbiO6i1Xk9v1yU4rvh9B4m3k3l3v2P9sU9u','user');

-- Optional sample webinars (UTC times)
INSERT INTO webinars (title,start_at,end_at,initiated_by) VALUES
('Team Intro', '2025-01-06 09:00:00', '2025-01-06 10:00:00', 1),
('Q1 Planning', '2025-02-10 13:00:00', '2025-02-10 14:00:00', 1),
('Customer Webinar', '2025-03-12 15:00:00', '2025-03-12 16:00:00', 2);
