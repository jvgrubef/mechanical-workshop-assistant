<?php
    include('inc/session.php');

    if ($_GET['page'] === 'logout') {
        session_destroy();
        header("Location: login.php");
        exit;
    };

    $baseDir  = './pages/';
    $pageGet  = $_GET['page'] ?? 'cashbook';
    $pagePath = realpath($baseDir . basename($pageGet));

    if (!$pagePath || strpos($pagePath, realpath($baseDir)) !== 0) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    };
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <link rel="icon" type="image/x-icon" href="img/favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>BIAMAC</title>
        <link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/list.css">
        <script>
            const path = "<?= $pageGet ?>";
            const today = "<?= date('Y-m-d') ?>";
        </script>
        <script src="js/mask.js"></script>
        <script src="js/index.js"></script>
    </head>
<body>
    <div class="side" id="side">
        <?php include('inc/header.php');?>
        <?php include('inc/widget.php');?>
        <?php include('inc/menu.php');?>
        <?php include('inc/footer.php');?>
    </div>
    <div class="wrapper">
        <?php include($pagePath . '/index.php');?>
    </div>
</body>