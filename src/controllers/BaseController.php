<?php
class BaseController {
    protected function renderView($view, $data = []) {
        extract($data);

        // Base path: src
        $baseDir = realpath(__DIR__ . '/../');

        // Absolute path to view
        $viewPath = $baseDir . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';

        // Absolute path to layout
        $layoutPath = $baseDir . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'main.php';

        if (file_exists($viewPath)) {
            $viewFile = $viewPath;
            if (file_exists($layoutPath)) {
                require $layoutPath;
            } else {
                die("Layout not found at $layoutPath");
            }
        } else {
            die("View not found at $viewPath");
        }
    }
}
