CREATE DATABASE IF NOT EXISTS supply_desk;
USE supply_desk;

-- Dropping Tables if they exist
-- For quick database resets

DROP TABLE IF EXISTS request_supplies;
DROP TABLE IF EXISTS supplies;
DROP TABLE IF EXISTS supply_categories;
DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;

-- Creating Tables

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

    FOREIGN KEY (departments_id) REFERENCES departments(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requesters_id INT NOT NULL,
    processors_id INT,

    requested_date DATETIME NOT NULL, -- When the request was made
    claimed_date DATETIME, -- When a processor claims and starts working on the request
    ready_date DATETIME, -- When the request is now ready for pickup
    finished_date DATETIME, -- When the request has either been picked up or denied
    
    status ENUM('Pending', 'Claimed', 'Ready For Pickup', 'Completed', 'Denied') NOT NULL DEFAULT 'Pending',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    requesters_message TEXT,
    processors_remark TEXT,

    FOREIGN KEY (requesters_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (processors_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
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
    price_per_unit DECIMAL(8, 2) NOT NULL,
    stock_quantity INT NOT NULL CHECK (stock_quantity >= 0),

    FOREIGN KEY (supply_categories_id) REFERENCES supply_categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE request_supplies (
    requests_id INT NOT NULL,
    supplies_id INT NOT NULL,
    supply_quantity INT NOT NULL CHECK (supply_quantity > 0),

    PRIMARY KEY (requests_id, supplies_id),
    FOREIGN KEY (requests_id) REFERENCES requests(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (supplies_id) REFERENCES supplies(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- Seeding Initial Data

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