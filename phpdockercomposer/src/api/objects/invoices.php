<?php

class Invoices
{
    private $conn;
    private $table_name = "invoice";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function fetchInvoice()
    {
        $query = "select * from $this->table_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;

    }

    function payInvoice($id, $data)
    {
        $sql = "SELECT amount, paid_amount FROM $this->table_name WHERE id = $id";
        $result = $this->conn->prepare($sql);
        $result->execute();
        if ($result->rowCount() > 0) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $row['paid_amount'] += $data['amount'];
                if ($row['paid_amount'] == $row['amount']) {
                    $sql = "UPDATE $this->table_name SET status='Paid' , paid_amount = {$row['paid_amount']} WHERE id=$id";
                    $stmt = $this->conn->prepare($sql);
                    if ($stmt->execute()) {
                        return $id;
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        echo "SQL Error: " . $errorInfo[2];
                        return false;
                    }
                } else
                    return false;
            }
        }
    }

    function processOverdueInvoices($data)
    {
        $sql = "SELECT id,amount, paid_amount FROM $this->table_name WHERE status = 'Pending'";
        $result = $this->conn->prepare($sql);
        $result->execute();
        if ($result->rowCount() > 0) {

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                if ($row['paid_amount'] < $row['amount']) {
                    if ($row['paid_amount'] > 0) {
                        $sql_update = "UPDATE $this->table_name SET status='Paid' WHERE id=?";
                    } elseif ($row['paid_amount'] == 0) {
                        $sql_update = "UPDATE $this->table_name SET status='Void' WHERE id=?";
                    }

                    if (isset($sql_update)) {
                        $stmt_update = $this->conn->prepare($sql_update);
                        if (!$stmt_update->execute([$row['id']])) {
                            die("Update failed: " . implode(", ", $stmt_update->errorInfo()));
                        }
                    }

                    // Calculate remaining amount and create new invoice

                    $remainingAmount = ($row['amount'] - $row['paid_amount']) + $data['late_fee'];
                    $invoiceDetails = array(
                        'amount' => $remainingAmount,
                        'paid_amount' => 0,
                        'due_date' => date('Y-m-d', strtotime('+30 days')) // Example due date, adjust as per your requirements,
                    );
                    $creationArray[] = $this->createInvoice($invoiceDetails);

                }
            }
        }
        return json_encode($creationArray);
    }

    public function createInvoice($details)
    {
        try {
            $query = "INSERT INTO " . $this->table_name . " (amount, paid_amount, due_date, status) VALUES (:amount, :paid_amount, :due_date, 'Pending')";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':amount', $details['amount']);
            $stmt->bindParam(':paid_amount', $details['paid_amount']);
            $stmt->bindParam(':due_date', $details['due_date']);

            if ($stmt->execute()) {
                $lastId = $this->conn->lastInsertId();
                return $lastId;
            } else {
                $errorInfo = $stmt->errorInfo();
                echo "SQL Error: " . $errorInfo[2];
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}