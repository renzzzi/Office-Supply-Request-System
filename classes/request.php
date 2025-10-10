<?php

/*

CREATE TABLE request (
    id INT AUTO_INCREMENT PRIMARY KEY;
    status ENUM('pending', 'approved', 'completed', 'denied') NOT NULL DEFAULT 'pending',
    request_date DATETIME NOT NULL
);

*/

require_once "database.php";

class Request extends Database
{
    public $id = "";
    public $status = "";
    public $request_date = "";

    public function createRequest()
    {
        $sql = "INSERT INTO request (request_date) VALUE (:request_date)";

        $query = $this->connect()->prepare($sql);
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