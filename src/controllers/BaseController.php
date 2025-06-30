<?php
class BaseController {
    protected function loadView(string $viewPath, array $data = []) {
        extract($data);

        // Capture the view content
        ob_start();

        $fullPath = __DIR__ . "/../views/$viewPath.php";
        if (file_exists($fullPath)) {
            require $fullPath;
        } else {
            echo "View '$viewPath' not found.";
            return;
        }
        $content = ob_get_clean();

        // Load the main layout, injecting title and content
        require __DIR__ . '/../views/layouts/main.php';
    }
}
