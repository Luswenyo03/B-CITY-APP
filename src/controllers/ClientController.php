<?php
// Include base controller and related models
require_once 'C:\xampp\htdocs\BCity app\src\controllers\BaseController.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Contact.php';

class ClientController extends BaseController {
    private $clientModel;

    // Constructor: initialize the client model
    public function __construct() {
        $this->clientModel = new Client();
    }

    // Default action: show the list of clients
    public function index() {
        // Get all clients and contacts
        $data['clients'] = $this->clientModel->getAll();
        $data['contacts'] = (new Contact())->getAll();

        // Add linked contacts to each client
        foreach ($data['clients'] as &$client) {
            $client['linked_contacts'] = $this->clientModel->getLinkedContacts($client['id']);
        }

        // Render the client list view
        $this->renderView('clients/index', $data);
    }

    // Handles client creation (form display and form processing)
    public function create() {
        // If form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $contact_ids = isset($_POST['contact_ids']) ? $_POST['contact_ids'] : [];
            $errors = [];

            // Basic validation
            if (empty($name)) {
                $errors[] = "Name is required.";
            }

            if (empty($errors)) {
                // Attempt to create client
                if ($this->clientModel->create($name, $contact_ids)) {
                    // Redirect on success
                    header('Location: ?controller=client&action=index');
                    exit;
                } else {
                    $data['error'] = "Failed to create client.";
                }
            } else {
                // Pass errors to view
                $data['error'] = implode('<br>', $errors);
            }

            // Pass data back to view for re-render
            $data['contacts'] = (new Contact())->getAll();
            $this->renderView('clients/create', $data);
        } else {
            // First-time GET request: show form
            $data['contacts'] = (new Contact())->getAll();
            $this->renderView('clients/create', $data);
        }
    }

    // AJAX endpoint: generate a client code from name input
    public function generateClientCode() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['name'])) {
            $name = trim($_GET['name']);

            // Ensure name contains at least 2 alphabetic characters
            if (strlen(preg_replace('/[^a-zA-Z]/', '', $name)) >= 2) {
                $client_code = $this->clientModel->generateClientCode($name);
                echo json_encode(['client_code' => $client_code]);
            } else {
                echo json_encode(['client_code' => '']);
            }
        } else {
            echo json_encode(['client_code' => '']);
        }
        exit;
    }

    // POST: link selected contacts to a client
    public function linkContacts() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $_POST['client_id'] ?? null;
            $contact_ids = $_POST['contact_ids'] ?? [];

            if ($client_id && !empty($contact_ids)) {
                $result = $this->clientModel->linkContacts($client_id, $contact_ids);
                $messages = [];

                // Success and duplicate info
                if (!empty($result['linked'])) {
                    $messages[] = "Contact(s) linked successfully.";
                }
                if (!empty($result['already_linked'])) {
                    $messages[] = "Some contact(s) are already linked.";
                }

                // Reload view with feedback
                $data['message'] = implode(' ', $messages);
                $data['clients'] = $this->clientModel->getAll();
                $data['contacts'] = (new Contact())->getAll();
                foreach ($data['clients'] as &$client) {
                    $client['linked_contacts'] = $this->clientModel->getLinkedContacts($client['id']);
                }

                $this->renderView('clients/index', $data);
                return;
            }

            // Fallback redirect if request is invalid
            header('Location: ?controller=client&action=index');
            exit;
        }
    }

    // AJAX: unlink a contact from a client
    public function unlinkContact() {
        $clientId = $_POST['client_id'] ?? null;
        $contactId = $_POST['contact_id'] ?? null;

        header('Content-Type: application/json');

        if ($clientId && $contactId) {
            $success = $this->clientModel->unlinkContact($clientId, $contactId);
            echo json_encode(['success' => (bool)$success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        }
        exit;
    }

    // AJAX: return all contacts linked to a given client
    public function getLinkedContacts() {
        $clientId = isset($_GET['client_id']) ? (int) $_GET['client_id'] : null;

        if ($clientId > 0) {
            $contacts = $this->clientModel->getContactsByClientId($clientId);
            header('Content-Type: application/json');
            echo json_encode($contacts);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid client ID']);
        }
    }
}
