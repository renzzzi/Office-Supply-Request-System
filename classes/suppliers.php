<?php

class Suppliers extends Database
{
    public $id = "";
    public $company_name = "";
    public $contact_person = "";
    public $contact_email = "";

    public function addSupplier()
    {
        $sql = "INSERT INTO supplier (company_name, contact_person, contact_email) 
                VALUES (:company_name, :contact_person, :contact_email)";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":company_name", $this->company_name);
        $query->bindParam(":contact_person", $this->contact_person);
        $query->bindParam(":contact_email", $this->contact_email);

        return $query->execute();
    }
}

?>