<?php
require_once __DIR__ . '/BaseModel.php';  // Correct path from same folder
// Removed: require_once 'Client.php';

class Contact extends BaseModel {
    public function getAll() {
        $stmt = $this->pdo->prepare("
            SELECT c.*, COUNT(cc.client_id) as client_count
            FROM contacts c
            LEFT JOIN client_contact cc ON c.id = cc.contact_id
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name, $email, $client_ids) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM contacts WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) return false;

        $stmt = $this->pdo->prepare("INSERT INTO contacts (name, email, created_at) VALUES (?, ?, NOW())");
        if ($stmt->execute([$name, $email])) {
            $contact_id = $this->pdo->lastInsertId();
            if (!empty($client_ids)) {
                $this->linkClients($contact_id, $client_ids);
            }
            return true;
        }

        return false;
    }

    public function unlinkClient($contact_id, $client_id) {
        $stmt = $this->pdo->prepare("DELETE FROM client_contact WHERE contact_id = ? AND client_id = ?");
        return $stmt->execute([$contact_id, $client_id]);
    }
    

    private function linkClients($contact_id, $client_ids) {
        foreach ($client_ids as $client_id) {
            $stmt = $this->pdo->prepare("INSERT IGNORE INTO client_contact (contact_id, client_id) VALUES (?, ?)");
            $stmt->execute([$contact_id, $client_id]);
        }
    }
}
