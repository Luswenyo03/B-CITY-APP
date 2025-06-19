<?php
require_once __DIR__ . '/BaseModel.php';

class Client extends BaseModel {

    // Unlink a specific contact from a client
    public function unlinkContact($clientId, $contactId) {
        $stmt = $this->pdo->prepare("DELETE FROM client_contact WHERE client_id = ? AND contact_id = ?");
        return $stmt->execute([$clientId, $contactId]);
    }

    // Get all contacts linked to a specific client
    public function getLinkedContacts($client_id) {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.surname, c.name, c.email
            FROM contacts c
            JOIN client_contact cc ON c.id = cc.contact_id
            WHERE cc.client_id = ?
        ");
        $stmt->execute([$client_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all clients along with the number of contacts linked to each
    public function getAll() {
        $stmt = $this->pdo->prepare("
            SELECT c.*, COUNT(cc.contact_id) as contact_count
            FROM clients c
            LEFT JOIN client_contact cc ON c.id = cc.client_id
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create a new client, optionally linking contacts
    public function create($name, $contact_ids) {
        $client_code = $this->generateClientCode($name); // Generate a unique client code
        $stmt = $this->pdo->prepare("INSERT INTO clients (name, client_code, created_at) VALUES (?, ?, NOW())");
        if ($stmt->execute([$name, $client_code])) {
            $client_id = $this->pdo->lastInsertId();
            if (!empty($contact_ids)) {
                $this->linkContacts($client_id, $contact_ids);
            }
            return true;
        }
        return false;
    }

    // Redundant: Similar to unlinkContact() above
    public function unlinkContactFromClient($clientId, $contactId) {
        $stmt = $this->pdo->prepare("DELETE FROM client_contact WHERE client_id = ? AND contact_id = ?");
        return $stmt->execute([$clientId, $contactId]);
    }

    // Link multiple contacts to a client, avoiding duplicates
    public function linkContacts($client_id, $contact_ids) {
        $alreadyLinked = [];
        $linkedNow = [];

        $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM client_contact WHERE client_id = ? AND contact_id = ?");
        $insertStmt = $this->pdo->prepare("INSERT INTO client_contact (client_id, contact_id) VALUES (?, ?)");

        foreach ($contact_ids as $contact_id) {
            $checkStmt->execute([$client_id, $contact_id]);
            $exists = $checkStmt->fetchColumn();

            if ($exists) {
                $alreadyLinked[] = $contact_id;
            } else {
                $insertStmt->execute([$client_id, $contact_id]);
                $linkedNow[] = $contact_id;
            }
        }

        return [
            'linked' => $linkedNow,
            'already_linked' => $alreadyLinked,
        ];
    }

    // Generate a unique client code based on name prefix
    public function generateClientCode($name) {
        // Step 1: Clean name to only letters and make uppercase

        $cleanName = strtoupper(preg_replace('/[^a-zA-Z]/', '', $name));
    
        // Step 2: Generate 3-letter prefix
        $prefix = substr($cleanName, 0, 3);
        $len = strlen($prefix);
    
        if ($len < 3) {
            $alphabet = range('A', 'Z');
            for ($i = 0; $i < 3 - $len; $i++) {
                $prefix .= $alphabet[$i];
            }
        }
    
        // Step 3: Find latest code with this prefix
        $stmt = $this->pdo->prepare("SELECT client_code FROM clients WHERE client_code LIKE ? ORDER BY client_code DESC LIMIT 1");
        $stmt->execute([$prefix . '%']);
        $lastCode = $stmt->fetchColumn();
    
        if ($lastCode) {
            // Extract numeric part and increment
            $lastNum = (int)substr($lastCode, -3);
            $nextNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '001';
        }
    
        return $prefix . $nextNum;
    }
    

    // Create a client using a manually specified code
    public function createWithCode($name, $client_code, $contact_ids) {
        $stmt = $this->pdo->prepare("INSERT INTO clients (name, client_code, created_at) VALUES (?, ?, NOW())");
        if ($stmt->execute([$name, $client_code])) {
            $client_id = $this->pdo->lastInsertId();
            if (!empty($contact_ids)) {
                $this->linkContacts($client_id, $contact_ids);
            }
            return true;
        }
        return false;
    }

    // Get client info by exact name
    public function getByName($name) {
        $stmt = $this->pdo->prepare("SELECT * FROM clients WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch all contacts linked to a specific client ID
    public function getContactsByClientId($clientId) {
        $query = "
            SELECT c.id, c.surname, c.name, c.email
            FROM contacts AS c
            INNER JOIN client_contact AS cc ON cc.contact_id = c.id
            WHERE cc.client_id = :client_id
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get only id, name, and client_code for dropdowns or fast lookup
    public function getAllClients() {
        $query = "SELECT id, name, client_code FROM clients ORDER BY name ASC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a client by ID (e.g., for linking feedback)
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
