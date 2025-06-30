<?php
require_once 'BaseController.php';

class ContactController extends BaseController {
    public function index() {
        // Here you can fetch contacts data from model or hardcode for now
        $contacts = [
            ['name' => 'Acme Corp', 'client_code' => 'AC123', 'linked_contacts' => 5],
            ['name' => 'Beta Ltd', 'client_code' => 'BL456', 'linked_contacts' => 3],
            ['name' => 'Gamma Inc', 'client_code' => 'GI789', 'linked_contacts' => 7],
        ];

        $this->loadView('contact/index', [
            'title' => 'Contact List',
            'contacts' => $contacts
        ]);
    }
}
