<?php
    date_default_timezone_set('America/Sao_Paulo');

    include('php/test.string.php');
    include('inc/router.php');
?>
<!DOCTYPE html>
<html lang="pt-br">
    <!-- Generated at <?= date('d/m/Y - H:i:s') ?> -->
    <head>
        <meta charset="UTF-8">
        <link rel="icon" type="image/x-icon" href="img/favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AOM</title>
        <link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/list.css">
        <script>
            const path = "<?= $page ?>";
            const permsLocal = <?= (Int)$permissionsPage ?>;
            const perms = <?= (Int)$adminLevel ?>;
            const today = "<?= date('Y-m-d') ?>";
        </script>
        <script src="js/mask.js"></script>
        <script src="js/bigDecimal.js"></script>
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
</html>