<?php

/*

CREATE TABLE request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    processor_id INT,
    department_id INT NOT NULL,
    status ENUM('pending', 'approved', 'completed', 'denied') NOT NULL DEFAULT 'pending',
    request_date DATETIME NOT NULL,
    processed_date DATETIME,

    FOREIGN KEY (requester_id) REFERENCES user(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (department_id) REFERENCES department(id)
        ON UPDATE CASCADE,
    FOREIGN KEY (processor_id) REFERENCES user(id)
        ON UPDATE CASCADE
);

*/

require_once "database.php";

class Request extends Database
{
    public $id = "";
    public $requester_id = "";
    public $processor_id = ""; // nullable
    public $department_id = "";
    public $status = ""; // default 'pending'
    public $request_date = "";
    public $processed_date = ""; // nullable

    public function addRequest()
    {
        $sql = "INSERT INTO request (requester_id, department_id, request_date) 
                VALUES (:requester_id, :department_id, :request_date)";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":requester_id", $this->requester_id);
        $query->bindParam(":department_id", $this->department_id);
        $query->bindParam(":request_date", $this->request_date);

        return $query->execute();
    }

    public function modifyRequestStatus($requestId = "", $newStatus = "pending")
    {
        $sql = "UPDATE request SET status = :status WHERE id = :id";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":status", $newStatus);
        $query->bindParam(":id", $requestId);

        return $query->execute();
    }
}

?>