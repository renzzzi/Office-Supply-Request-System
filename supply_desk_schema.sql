CREATE DATABASE IF NOT EXISTS supply_desk;
USE supply_desk;

DROP TABLE IF EXISTS stock_logs;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS request_supplies;
DROP TABLE IF EXISTS supplies;
DROP TABLE IF EXISTS supply_categories;
DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    departments_id INT NOT NULL,
    role ENUM('Requester', 'Processor', 'Admin') NOT NULL DEFAULT 'Requester',

    CONSTRAINT fk_users_departments
        FOREIGN KEY (departments_id) REFERENCES departments(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requesters_id INT NOT NULL,
    processors_id INT,
    released_to VARCHAR(100),

    requested_date DATETIME NOT NULL,
    claimed_date DATETIME,
    ready_date DATETIME,
    finished_date DATETIME,
    
    status ENUM('Pending', 'Claimed', 'Ready For Pickup', 'Released', 'Denied') NOT NULL DEFAULT 'Pending',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    requesters_message TEXT,
    processors_remark TEXT,

    CONSTRAINT fk_requests_users_requester
        FOREIGN KEY (requesters_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_requests_users_processor
        FOREIGN KEY (processors_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

CREATE TABLE supply_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE supplies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supply_categories_id INT NOT NULL,
    name VARCHAR(50) UNIQUE NOT NULL,
    unit_of_supply VARCHAR(30) NOT NULL,
    price_per_unit DECIMAL(8, 2) NOT NULL CHECK (price_per_unit > 0.0),
    stock_quantity INT NOT NULL CHECK (stock_quantity >= 0),

    CONSTRAINT fk_supplies_supply_categories
        FOREIGN KEY (supply_categories_id) REFERENCES supply_categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE request_supplies (
    requests_id INT NOT NULL,
    supplies_id INT NOT NULL,
    supply_quantity INT NOT NULL CHECK (supply_quantity > 0),

    PRIMARY KEY (requests_id, supplies_id),

    CONSTRAINT fk_request_supplies_requests
        FOREIGN KEY (requests_id) REFERENCES requests(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_request_supplies_supplies
        FOREIGN KEY (supplies_id) REFERENCES supplies(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE stock_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplies_id INT NOT NULL,
    requests_id INT NULL,
    change_amount INT NOT NULL,
    new_quantity INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_stock_logs_supplies
        FOREIGN KEY (supplies_id) REFERENCES supplies(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_stock_logs_requests
        FOREIGN KEY (requests_id) REFERENCES requests(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    ip_address VARCHAR(45) NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_activity_logs_users
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

INSERT INTO departments (name) VALUES
('Marketing'),
('Operations'),
('IT');

INSERT INTO supply_categories (name) VALUES 
('Writing Supplies'),
('Paper Products'),
('Electronics');

INSERT INTO users (first_name, last_name, email, password_hash, departments_id, role) VALUES
('re', 're', 're@re.re', '$2y$10$Mc7x5V7o5griHt9ddJDu6e/FDslFoAjMdN2fgDdEoethCZd4plQfW', 1, 'Requester'),
('pr', 'pr', 'pr@pr.pr', '$2y$10$1sTu0XqSQFtQLh6qCsMPY.3F0eP50879l9Yw46Bxgd5J48Og98u5W', 2, 'Processor'),
('ad', 'ad', 'ad@ad.ad', '$2y$10$Jwy6bUyLhsIKyjoq25hrSeoLOuFsdIrMQFomGw247x6A3wMwArc2S', 3, 'Admin');

INSERT INTO supplies (supply_categories_id, name, unit_of_supply, price_per_unit, stock_quantity) VALUES
(1, 'Ballpoint Pen', 'Piece', 8.50, 100),
(1, 'Marker', 'Piece', 10.00, 50),
(2, 'A4 Paper Ream', 'Ream', 450.25, 30),
(2, 'Sticky Notes', 'Dozen', 15.00, 40),
(3, 'Wireless Mouse', 'Piece', 75.50, 20),
(3, 'Keyboard', 'Piece', 180.00, 15);