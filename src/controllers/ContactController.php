<?php
// Include required base controller and model classes
require_once __DIR__ .'/BaseController.php';
require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../models/Client.php';

class ContactController extends BaseController {
    private $contactModel;

    // Constructor initializes the contact model
    public function __construct() {
        $this->contactModel = new Contact();
    }

    // Default method: list all contacts with client info
    public function index() {
        $data['contacts'] = $this->contactModel->getAll(); // Get all contacts
        $data['clients'] = (new Client())->getAll();       // Get all clients
        $this->renderView('contacts/index', $data);        // Load the view
    }

    // Handle creation of a new contact
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize and collect POST data
            $surname = trim($_POST['surname'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $client_ids = $_POST['client_ids'] ?? [];

            $errors = [];

            // Validate surname
            if (empty($surname) || !preg_match("/^[a-zA-Z\s]+$/", $surname)) {
                $errors[] = "Surname is required and must contain only letters and spaces.";
            }

            // Validate name
            if (empty($name) || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
                $errors[] = "Name is required and must contain only letters and spaces.";
            }

            // Validate email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "A valid email is required.";
            }

            // If validation passes
            if (empty($errors)) {
                if ($this->contactModel->create($surname, $name, $email, $client_ids)) {
                    header('Location: ?controller=contact&action=index');
                    exit;
                } else {
                    $data['error'] = "Failed to create contact or email already exists.";
                }
            } else {
                $data['error'] = implode('<br>', $errors);
            }

            // Reload form with error
            $data['clients'] = (new Client())->getAll();
            $this->renderView('contacts/create', $data);
        } else {
            // Initial GET: show form
            $data['clients'] = (new Client())->getAll();
            $this->renderView('contacts/create', $data);
        }
    }

    // API: Unlink a contact from a client
    public function unlinkContact() {
        $contactId = $_POST['contact_id'] ?? null;
        $clientId = $_POST['client_id'] ?? null;

        // Return JSON response
        if ($contactId && $clientId) {
            $success = $this->contactModel->unlinkClient($contactId, $clientId);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        }
        exit;
    }

    // API: Get clients that are not yet linked to this contact
    public function getAvailableClients() {
        $contactId = $_GET['contact_id'];
        $contactModel = new Contact();
        $clients = $contactModel->getUnlinkedClients($contactId);
        echo json_encode($clients);
    }

// Handle linking multiple clients to a contact
public function linkClients() {
    $contactId = $_POST['contact_id'] ?? null;
    $clientIds = $_POST['client_ids'] ?? [];

    $contactModel = new Contact();
    $clientModel = new Client();

    $linked = [];
    $alreadyLinked = [];
    $failed = [];

    if ($contactId && !empty($clientIds)) {
        foreach ($clientIds as $clientId) {
            $status = $contactModel->linkToClient($contactId, $clientId);
            $client = $clientModel->getById($clientId);

            if (!$client) continue; // Skip if client not found

            $clientName = htmlspecialchars($client['name']);

            switch ($status) {
                case 'exists':
                    $alreadyLinked[] = $clientName;
                    break;
                case 'linked':
                    $linked[] = $clientName;
                    break;
                default:
                    $failed[] = $clientName;
                    break;
            }
        }
    } else {
        $data['message'] = "Missing contact or clients.";
    }

    // Build grouped message
    $messageParts = [];

    if (!empty($linked)) {
        $messageParts[] = 'Client(s) linked successfully: ' . implode(', ', array_unique($linked)) . '.';
    }
    if (!empty($alreadyLinked)) {
        $messageParts[] = 'Client(s) already linked: ' . implode(', ', array_unique($alreadyLinked)) . '.';
    }
    if (!empty($failed)) {
        $messageParts[] = 'Failed to link client(s): ' . implode(', ', array_unique($failed)) . '.';
    }

    // Prepare final view data
    $data['contacts'] = $contactModel->getAllWithClientCounts();
    $data['clients'] = $clientModel->getAll();
    $data['message'] = implode('<br>', $messageParts);

    $this->renderView('contacts/index', $data);
}


    // API: Return contact details and its linked clients
    public function getLinkedClients() {
        $contactId = $_GET['contact_id'] ?? null;

        if ($contactId) {
            $pdo = $this->contactModel->getPdo();

            // Get contact info
            $stmt = $pdo->prepare("SELECT name, surname, email FROM contacts WHERE id = ?");
            $stmt->execute([$contactId]);
            $contact = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get linked clients for the contact
            $stmt2 = $pdo->prepare("
                SELECT cl.id, cl.name, cl.client_code
                FROM clients cl
                INNER JOIN client_contact cc ON cc.client_id = cl.id
                WHERE cc.contact_id = ?
            ");
            $stmt2->execute([$contactId]);
            $clients = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            // Respond with JSON
            header('Content-Type: application/json');
            echo json_encode([
                'contact' => $contact,
                'clients' => $clients
            ]);
        } else {
            echo json_encode([
                'contact' => null,
                'clients' => []
            ]);
        }

        exit;
    }
}
