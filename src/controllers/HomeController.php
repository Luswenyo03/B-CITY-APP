<?php
// Include BaseController to make sure it loads (optional if already loaded in index.php)
require_once 'BaseController.php';

class HomeController extends BaseController {
    public function index() {
        $this->loadView('home/index', [
            'title' => 'Home',
            'message' => 'Welcome to the BinaryCity homepage!'
        ]);
    }
    
}
