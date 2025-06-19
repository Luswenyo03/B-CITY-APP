<?php
// Include the Client model (used to fetch or create client data)
require_once __DIR__ . '/../models/Client.php';

class LinkController extends BaseController {

    // Default method: loads a page that displays all contacts
    public function index() {
        $contacts = (new Contact())->getAll(); // Fetch all contacts
        $this->renderView('link/index', ['contacts' => $contacts]); // Load the view with contacts
    }

    // AJAX endpoint: Checks if a client exists by name, or returns false
    public function checkOrCreateClient() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');

            // Return early if name is empty
            if (!$name) {
                echo json_encode(['exists' => false]);
                return;
            }

            $clientModel = new Client();
            $client = $clientModel->getByName($name); // Try to fetch client by name

            if ($client) {
                // Client exists, return true with their code
                echo json_encode(['exists' => true, 'client_code' => $client['client_code']]);
            } else {
                // Client not found
                echo json_encode(['exists' => false]);
            }
            exit;
        }
    }

    // AJAX endpoint: Creates a new client by name
    public function createClient() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');

            // Validate name
            if (!$name) {
                echo json_encode(['success' => false]);
                return;
            }

            $clientModel = new Client();
            $created = $clientModel->create($name, []); // Create client with no linked contacts

            if ($created) {
                // After creation, fetch the newly created client
                $client = $clientModel->getByName($name);
                echo json_encode(['success' => true, 'client_code' => $client['client_code']]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;
        }
    }
}
