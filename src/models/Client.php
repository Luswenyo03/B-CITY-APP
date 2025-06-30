<?php
require_once __DIR__ . '/BaseModel.php';

class Client extends BaseModel {
    // ... other existing methods ...

    public function getLinkedContacts($client_id) {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.name
            FROM contacts c
            JOIN client_contact cc ON c.id = cc.contact_id
            WHERE cc.client_id = ?
        ");
        $stmt->execute([$client_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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

    public function create($name, $contact_ids) {
        $client_code = $this->generateClientCode($name);
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

    public function unlinkContact($client_id, $contact_id) {
        $stmt = $this->pdo->prepare("DELETE FROM client_contact WHERE client_id = ? AND contact_id = ?");
        $result = $stmt->execute([$client_id, $contact_id]);
        if (!$result) {
            error_log("Failed to unlink contact. Client ID: $client_id, Contact ID: $contact_id");
        }
        return $result;
    }

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

    public function generateClientCode($name) {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 2) ?: 'XX');
        $stmt = $this->pdo->prepare("SELECT MAX(client_code) as max_code FROM clients WHERE client_code LIKE ?");
        $stmt->execute([$prefix . '%']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $max_code = $row['max_code'] ?: ($prefix . '000');
        $num = intval(substr($max_code, 2)) + 1;
        return $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

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


    public function getByName($name) {
    $stmt = $this->pdo->prepare("SELECT * FROM clients WHERE name = ?");
    $stmt->execute([$name]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    
    
}