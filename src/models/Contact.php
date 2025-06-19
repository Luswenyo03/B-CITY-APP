<?php
require_once __DIR__ . '/BaseModel.php';

class Contact extends BaseModel {

    // Get all contacts, including a count of linked clients
    public function getAll() {
        $stmt = $this->pdo->prepare("
            SELECT c.*, COUNT(cc.client_id) as client_count
            FROM contacts c
            LEFT JOIN client_contact cc ON c.id = cc.contact_id
            GROUP BY c.id
            ORDER BY c.surname ASC, c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create a new contact, optionally linking to clients
    public function create($surname, $name, $email, $client_ids) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        // Ensure the email is unique
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM contacts WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) return false;

        // Insert new contact
        $stmt = $this->pdo->prepare("
            INSERT INTO contacts (surname, name, email, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        if ($stmt->execute([$surname, $name, $email])) {
            $contact_id = $this->pdo->lastInsertId();

            // Link to clients if provided
            if (!empty($client_ids)) {
                $this->linkClients($contact_id, $client_ids);
            }
            return true;
        }
        return false;
    }

    // Internal method: link multiple clients to a contact
    private function linkClients($contact_id, $client_ids) {
        foreach ($client_ids as $client_id) {
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO client_contact (contact_id, client_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$contact_id, $client_id]);
        }
    }

    // Unlink a client from a contact
    public function unlinkClient($contact_id, $client_id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM client_contact
            WHERE contact_id = ? AND client_id = ?
        ");
        return $stmt->execute([$contact_id, $client_id]);
    }

    // Return clients NOT linked to a specific contact
    public function getUnlinkedClients($contactId) {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.name, c.client_code 
            FROM clients c
            WHERE c.id NOT IN (
                SELECT client_id FROM client_contact WHERE contact_id = ?
            )
        ");
        $stmt->execute([$contactId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Link a contact to a client if not already linked
    public function linkToClient($contactId, $clientId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM client_contact
            WHERE contact_id = ? AND client_id = ?
        ");
        $stmt->execute([$contactId, $clientId]);

        if ($stmt->fetchColumn() > 0) {
            return 'exists';
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO client_contact (contact_id, client_id)
            VALUES (?, ?)
        ");
        return $stmt->execute([$contactId, $clientId]) ? 'linked' : 'failed';
    }

    // Get all contacts with their client counts and detailed linked clients
    public function getAllWithClientCounts() {
        $stmt = $this->pdo->query("
            SELECT c.*, COUNT(cc.client_id) AS client_count
            FROM contacts c
            LEFT JOIN client_contact cc ON c.id = cc.contact_id
            GROUP BY c.id
        ");
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add linked client details to each contact
        foreach ($contacts as &$contact) {
            $stmt2 = $this->pdo->prepare("
                SELECT cl.id, cl.name, cl.client_code
                FROM clients cl
                INNER JOIN client_contact cc ON cc.client_id = cl.id
                WHERE cc.contact_id = ?
            ");
            $stmt2->execute([$contact['id']]);
            $contact['linked_clients'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        return $contacts;
    }

    // Get all clients linked to a specific contact
    public function getLinkedClients($contactId) {
        $stmt = $this->pdo->prepare("
            SELECT cl.id, cl.name, cl.client_code
            FROM clients cl
            INNER JOIN client_contact cc ON cc.client_id = cl.id
            WHERE cc.contact_id = ?
        ");
        $stmt->execute([$contactId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get full details of a contact
    public function getContactDetails($contactId) {
        $stmt = $this->pdo->prepare("
            SELECT surname, name, email
            FROM contacts
            WHERE id = ?
        ");
        $stmt->execute([$contactId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Expose PDO connection (used in controllers if needed)
    public function getPdo() {
        return $this->pdo;
    }
}
