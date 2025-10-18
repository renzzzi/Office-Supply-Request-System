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
        $this->conn = new PDO("mysql:host = $this->host; dbname = $this->db_name", 
                              $this->username, $this->password);
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
    departments_id INT NOT NULL,
    role ENUM('requester', 'processor', 'admin') NOT NULL DEFAULT 'requester',

    FOREIGN KEY (departments_id) REFERENCES departments(id)
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
        ON UPDATE CASCADE,
    FOREIGN KEY (processors_id) REFERENCES users(id)
        ON UPDATE CASCADE
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requesters_id INT NOT NULL,
    processors_id INT,
    status ENUM('pending', 'approved', 'completed', 'denied') NOT NULL DEFAULT 'pending',
    request_date DATETIME NOT NULL,
    processed_date DATETIME,

    FOREIGN KEY (requesters_id) REFERENCES users(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (processors_id) REFERENCES users(id)
        ON UPDATE CASCADE
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
    stock_quantity INT,

    FOREIGN KEY (supply_categories_id) REFERENCES supply_categories(id)
        ON UPDATE CASCADE
);

CREATE TABLE purchase_orders_supplies(
    purchase_orders_id INT NOT NULL,
    supplies_id INT NOT NULL,
    supply_quantity INT NOT NULL CHECK (supply_quantity > 0),
    price_per_unit DECIMAL(8, 2) NOT NULL,

    PRIMARY KEY (purchase_orders_id, supplies_id),
    FOREIGN KEY (purchase_orders_id) REFERENCES purchase_orders(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (supplies_id) REFERENCES supplies(id)
        ON UPDATE CASCADE
);

CREATE TABLE request_supplies (
    requests_id INT NOT NULL,
    supplies_id INT NOT NULL,
    supply_quantity INT NOT NULL CHECK (supply_quantity > 0),

    PRIMARY KEY (requests_id, supplies_id),
    FOREIGN KEY (requests_id) REFERENCES requests(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (supplies_id) REFERENCES supplies(id)
        ON UPDATE CASCADE
);

*/

?>