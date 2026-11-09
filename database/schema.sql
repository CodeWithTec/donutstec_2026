-- ============================================================
-- DonutsTecLiberia Database Schema
-- MySQL 8.0+ | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS donutstec_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE donutstec_db;

-- ------------------------------------------------------------
-- USERS (admin + client in one table, differentiated by role)
-- ------------------------------------------------------------
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(120) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password      VARCHAR(255) NOT NULL,
  phone         VARCHAR(30) DEFAULT NULL,
  company_name  VARCHAR(150) DEFAULT NULL,
  role          ENUM('admin','client') NOT NULL DEFAULT 'client',
  status        ENUM('active','pending','banned') NOT NULL DEFAULT 'active',
  avatar        VARCHAR(255) DEFAULT NULL,
  last_login    DATETIME DEFAULT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_role (role),
  INDEX idx_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PROJECTS
-- ------------------------------------------------------------
CREATE TABLE projects (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id     INT UNSIGNED NOT NULL,
  title         VARCHAR(200) NOT NULL,
  description   TEXT DEFAULT NULL,
  service_type  VARCHAR(100) DEFAULT NULL,
  status        ENUM('pending','in_progress','on_hold','completed','cancelled') NOT NULL DEFAULT 'pending',
  progress      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  budget        DECIMAL(12,2) DEFAULT NULL,
  start_date    DATE DEFAULT NULL,
  deadline      DATE DEFAULT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_project_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_status (status),
  INDEX idx_client (client_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- MILESTONES (per project)
-- ------------------------------------------------------------
CREATE TABLE milestones (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id    INT UNSIGNED NOT NULL,
  title         VARCHAR(200) NOT NULL,
  description   TEXT DEFAULT NULL,
  status        ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  due_date      DATE DEFAULT NULL,
  completed_at  DATETIME DEFAULT NULL,
  sort_order    INT NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_milestone_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  INDEX idx_project (project_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PROJECT FILES / DELIVERABLES
-- ------------------------------------------------------------
CREATE TABLE project_files (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id    INT UNSIGNED NOT NULL,
  uploaded_by   INT UNSIGNED NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name   VARCHAR(255) NOT NULL,
  file_size     INT UNSIGNED DEFAULT NULL,
  mime_type     VARCHAR(120) DEFAULT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_file_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_file_user    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- MESSAGES (per-project chat between client & admin)
-- ------------------------------------------------------------
CREATE TABLE messages (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_id     INT UNSIGNED NOT NULL,
  receiver_id   INT UNSIGNED NOT NULL,
  project_id    INT UNSIGNED DEFAULT NULL,
  body          TEXT NOT NULL,
  is_read       TINYINT(1) NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_msg_sender   FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_project  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
  INDEX idx_thread (sender_id, receiver_id, project_id),
  INDEX idx_read (receiver_id, is_read)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- SUPPORT TICKETS
-- ------------------------------------------------------------
CREATE TABLE tickets (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id     INT UNSIGNED NOT NULL,
  subject       VARCHAR(200) NOT NULL,
  message       TEXT NOT NULL,
  status        ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  priority      ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ticket_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_status (status)
) ENGINE=InnoDB;

CREATE TABLE ticket_replies (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_id     INT UNSIGNED NOT NULL,
  user_id       INT UNSIGNED NOT NULL,
  body          TEXT NOT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reply_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
  CONSTRAINT fk_reply_user   FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- QUOTE REQUESTS (from public site)
-- ------------------------------------------------------------
CREATE TABLE quotes (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  phone         VARCHAR(30) DEFAULT NULL,
  service_type  VARCHAR(100) DEFAULT NULL,
  budget_range  VARCHAR(50) DEFAULT NULL,
  details       TEXT NOT NULL,
  status        ENUM('new','contacted','converted','rejected') NOT NULL DEFAULT 'new',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- BLOG POSTS
-- ------------------------------------------------------------
CREATE TABLE blog_posts (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  author_id     INT UNSIGNED NOT NULL,
  title         VARCHAR(200) NOT NULL,
  slug          VARCHAR(220) NOT NULL UNIQUE,
  excerpt       VARCHAR(500) DEFAULT NULL,
  content       LONGTEXT NOT NULL,
  image         VARCHAR(255) DEFAULT NULL,
  status        ENUM('draft','published') NOT NULL DEFAULT 'draft',
  views         INT UNSIGNED NOT NULL DEFAULT 0,
  published_at  DATETIME DEFAULT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_blog_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PORTFOLIO
-- ------------------------------------------------------------
CREATE TABLE portfolio (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title         VARCHAR(200) NOT NULL,
  category      VARCHAR(100) DEFAULT NULL,
  description   TEXT DEFAULT NULL,
  image         VARCHAR(255) DEFAULT NULL,
  project_url   VARCHAR(255) DEFAULT NULL,
  is_featured   TINYINT(1) NOT NULL DEFAULT 0,
  sort_order    INT NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TESTIMONIALS
-- ------------------------------------------------------------
CREATE TABLE testimonials (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_name   VARCHAR(120) NOT NULL,
  client_role   VARCHAR(120) DEFAULT NULL,
  content       TEXT NOT NULL,
  rating        TINYINT UNSIGNED NOT NULL DEFAULT 5,
  is_approved   TINYINT(1) NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- INVOICES
-- ------------------------------------------------------------
CREATE TABLE invoices (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id    INT UNSIGNED NOT NULL,
  invoice_no    VARCHAR(40) NOT NULL UNIQUE,
  amount        DECIMAL(12,2) NOT NULL,
  currency      VARCHAR(5) NOT NULL DEFAULT 'USD',
  status        ENUM('unpaid','paid','overdue','cancelled') NOT NULL DEFAULT 'unpaid',
  due_date      DATE DEFAULT NULL,
  paid_at       DATETIME DEFAULT NULL,
  notes         TEXT DEFAULT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_invoice_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- NOTIFICATIONS
-- ------------------------------------------------------------
CREATE TABLE notifications (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL,
  title         VARCHAR(200) NOT NULL,
  message       TEXT DEFAULT NULL,
  link          VARCHAR(255) DEFAULT NULL,
  is_read       TINYINT(1) NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- NEWSLETTER SUBSCRIBERS
-- ------------------------------------------------------------
CREATE TABLE newsletter (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(150) NOT NULL UNIQUE,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- SITE SETTINGS (key/value)
-- ------------------------------------------------------------
CREATE TABLE settings (
  setting_key   VARCHAR(80) PRIMARY KEY,
  setting_value TEXT DEFAULT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ACTIVITY LOGS
-- ------------------------------------------------------------
CREATE TABLE activity_logs (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED DEFAULT NULL,
  action        VARCHAR(200) NOT NULL,
  ip_address    VARCHAR(45) DEFAULT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- DEFAULT SITE SETTINGS
-- ------------------------------------------------------------
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name',       'DonutsTecLiberia'),
('site_tagline',    'Building Software That Works For You'),
('site_email',      'info@donutstec.com'),
('site_phone',      '+231 XXX XXX XXXX'),
('site_address',    'Monrovia, Liberia'),
('facebook_url',    '#'),
('twitter_url',     '#'),
('linkedin_url',    '#'),
('instagram_url',   '#');

-- ------------------------------------------------------------
-- DEFAULT ADMIN ACCOUNT
-- Email: admin@donutstec.com  |  Password: Admin@123
-- (CHANGE PASSWORD IMMEDIATELY AFTER FIRST LOGIN)
-- ------------------------------------------------------------
INSERT INTO users (full_name, email, password, role, status)
VALUES (
  'DonutsTec Admin',
  'admin@donutstec.com',
  '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JQj1aC9U4yZ6x1x1x1x1',
  'admin',
  'active'
);