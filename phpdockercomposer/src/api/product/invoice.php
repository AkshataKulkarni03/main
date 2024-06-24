<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once('../config/database.php');
require_once('../objects/invoices.php');

class InvoiceAPI
{
    private $db;
    private $obj;

    public function __construct($db)
    {
        $this->db = $db;
        $this->obj = new Invoices($db);
    }

    public function handleRequest()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->handleGetRequest();
                break;
            case 'POST':
                $this->handlePostRequest();
                break;
            default:
                http_response_code(405);
                echo json_encode(array('message' => 'Method Not Allowed'));
                break;
        }
    }

    function handleGetRequest()
    {
        $stmt = $this->obj->fetchInvoice();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode(array('data' => $rows));
        } else {
            http_response_code(404);
            echo json_encode(array('message' => "No invoices found"));
        }
    }

    function handlePostRequest()
    {
        $invoiceId = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : null;
        $action = isset($_GET['action']) ? $_GET['action'] : null;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!empty($invoiceId) && $action == 'payments') {
            $this->handlePayments($invoiceId, $data);
        } elseif ($action == 'process-overdue') {
            $this->handleProcessOverdue($data);
        } else {
            $this->handleCreateInvoice($data);
        }
    }

    function handlePayments($invoiceId, $data)
    {
        $result = $this->obj->payInvoice($invoiceId, $data);
        if ($result) {
            http_response_code(200);
            echo json_encode(array('id' => $result, 'message' => 'Paid'));
        } else {
            http_response_code(400);
            echo json_encode(array('message' => "Amount is not fully paid"));
        }
    }

    function handleProcessOverdue($data)
    {
        $result = $this->obj->processOverdueInvoices($data);
        if ($result) {
            http_response_code(200);
            echo json_encode(array('id' => $result, 'message' => 'New Invoice created'));
        } else {
            http_response_code(400);
            echo json_encode(array('message' => 'Failed to process overdue invoices'));
        }
    }

    function handleCreateInvoice($data)
    {
        if (empty($data['amount']) || ($data['amount'] == 0) || empty($data['due_date'])) {
            http_response_code(400);
            echo json_encode(array('message' => 'Missing required parameters'));
            return;
        }

        $invoiceDetails = array(
            'amount' => $data['amount'],
            'paid_amount' => 0,
            'due_date' => $data['due_date']
        );

        $result = $this->obj->createInvoice($invoiceDetails);
        if ($result) {
            http_response_code(200);
            echo json_encode(array('id' => $result));
        } else {
            http_response_code(400);
            echo json_encode(array('message' => "Invoice was not created"));
        }
    }
}

$database = new Database();
$db = $database->getConnection();
$api = new InvoiceAPI($db);
$api->handleRequest();
?>

