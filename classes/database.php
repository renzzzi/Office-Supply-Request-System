<?php

class Database
{
    private $host = "127.0.0.1";
    private $username = "root";
    private $password = "";
    private $db_name = "supply_desk";

    protected $conn;

    protected function connect()
    {
        $this->conn = new PDO("mysql:host = $this->host; dbname = $this->db_name", $this->username, $this->password);
        return $this->conn;
    }
}

/*

CREATE DATABASE supply_desk;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department_id INT NOT NULL,
    role ENUM('requester', 'processor', 'admin') NOT NULL DEFAULT 'requester',

    FOREIGN KEY (department_id) REFERENCES department(id)
);

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    processor_id INT NOT NULL,
    order_date DATETIME NOT NULL,
    received_date DATETIME,
    status ENUM('pending', 'received', 'cancelled') NOT NULL DEFAULT 'pending',

    FOREIGN KEY (supplier_id) REFERENCES supplier(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (processor_id) REFERENCES user(id)
        ON UPDATE CASCADE
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    processor_id INT,
    status ENUM('pending', 'approved', 'completed', 'denied') NOT NULL DEFAULT 'pending',
    request_date DATETIME NOT NULL,
    processed_date DATETIME,

    FOREIGN KEY (requester_id) REFERENCES user(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (processor_id) REFERENCES user(id)
        ON UPDATE CASCADE
);

CREATE TABLE supplies_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE supplies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supply_category_id INT NOT NULL,
    name VARCHAR(50) UNIQUE NOT NULL,
    unit_of_supply VARCHAR(30) NOT NULL,
    price_per_unit DECIMAL(8, 2) NOT NULL,
    stock_quantity INT,

    FOREIGN KEY (supply_category_id) REFERENCES supply_category(id)
        ON UPDATE CASCADE
);

CREATE TABLE purchase_orders_supplies(
    purchase_order_id INT NOT NULL,
    supply_id INT NOT NULL,
    supply_quantity INT NOT NULL CHECK (supply_quantity > 0),
    price_per_unit DECIMAL(8, 2) NOT NULL,

    PRIMARY KEY (purchase_order_id, supply_id),
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_order(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (supply_id) REFERENCES supply(id)
        ON UPDATE CASCADE
);

CREATE TABLE request_supplies (
    request_id INT NOT NULL,
    supply_id INT NOT NULL,
    supply_quantity INT NOT NULL CHECK (supply_quantity > 0),

    PRIMARY KEY (request_id, supply_id),
    FOREIGN KEY (request_id) REFERENCES request(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (supply_id) REFERENCES supply(id)
        ON UPDATE CASCADE
);

*/

?>