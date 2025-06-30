<?php
$title = isset($title) ? $title : 'Client Management';
?>
<!DOCTYPE html>
<html>
<link rel="stylesheet" href="/BCity app/public/css/style.css">

<head>
    <title><?php echo htmlspecialchars($title); ?></title>
</head>
<body>
    <header><?php echo $title; ?></header>
    <nav>
        <a href="?controller=client&action=index">Clients</a> |
        <a href="?controller=contact&action=index">Contacts</a>
    </nav>
    <main>
<?php
if (isset($view)) {
    require_once __DIR__ . "/../$view.php";
} elseif (isset($clients)) {
    require_once __DIR__ . '/../clients/index.php';
} elseif (isset($contacts)) {
    require_once __DIR__ . '/../contacts/index.php';
} elseif (isset($error)) {
    echo "<p class='error'>$error</p>";
}
?>
</main>
    <footer>Copyright 2025</footer>
    <script src="/BCity app/public/js/script.js"></script>
</body>
</html>