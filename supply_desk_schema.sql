CREATE DATABASE IF NOT EXISTS supply_desk;
USE supply_desk;

-- Dropping Tables if they exist
-- For quick database resets

DROP TABLE IF EXISTS department_inventory;
DROP TABLE IF EXISTS request_supplies;
DROP TABLE IF EXISTS purchase_orders_supplies;
DROP TABLE IF EXISTS supplies;
DROP TABLE IF EXISTS supply_categories;
DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS purchase_orders;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS departments;

-- Creating Tables

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    departments_id INT NOT NULL,
    roles_id INT NOT NULL DEFAULT 1,
    status ENUM('active', 'inactive', 'removed') NOT NULL DEFAULT 'active',

    FOREIGN KEY (departments_id) REFERENCES departments(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (roles_id) REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    suppliers_id INT NOT NULL,
    processors_id INT NOT NULL,
    order_date DATETIME NOT NULL,
    received_date DATETIME,
    status ENUM('pending', 'received', 'cancelled') NOT NULL DEFAULT 'pending',

    FOREIGN KEY (suppliers_id) REFERENCES suppliers(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (processors_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requesters_id INT NOT NULL,
    processors_id INT,
    departments_id INT NOT NULL,
    request_date DATETIME NOT NULL,
    processed_date DATETIME,
    status ENUM('pending', 'in_progress', 'completed', 'denied') NOT NULL DEFAULT 'pending',

    FOREIGN KEY (requesters_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (departments_id) REFERENCES departments(id)
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

    FOREIGN KEY (supply_categories_id) REFERENCES supply_categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE purchase_orders_supplies(
    purchase_orders_id INT NOT NULL,
    supplies_id INT NOT NULL,
    supply_quantity INT NOT NULL CHECK (supply_quantity > 0),
    price_per_unit DECIMAL(8, 2) NOT NULL,

    PRIMARY KEY (purchase_orders_id, supplies_id),
    FOREIGN KEY (purchase_orders_id) REFERENCES purchase_orders(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (supplies_id) REFERENCES supplies(id)
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

CREATE TABLE department_inventories (
    departments_id INT NOT NULL,
    supplies_id INT NOT NULL,
    supply_quantity INT NOT NULL CHECK (supply_quantity > 0),

    PRIMARY KEY (departments_id, supplies_id),
    FOREIGN KEY (departments_id) REFERENCES departments(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (supplies_id) REFERENCES supplies(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- Seeding Initial Data

INSERT INTO departments (name) VALUES
('IT'),
('Marketing'),
('Sales');

INSERT INTO roles (name) VALUES 
('Requester'),
('Processor'),
('Admin');

INSERT INTO supply_categories (name) VALUES 
('Writing Supplies'),
('Paper Products'),
('Electronics');