<?php 
    session_start();

    if(isset($_SESSION['user']['id'])) {
        header("Location: index.php");
        exit;
    };
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <link rel="icon" type="image/x-icon" href="img/favicon.ico">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/login.css">
        <script src="js/login.js"></script>
        <title>Login</title>
    </head>
    <body>
        <div class="login-wrapper" id="login">
            <h1>AOM</h1>
            <p class="form-message">BEM-VINDO</p>
            <form class="form-content">
                <label class="form-input-text">
                    <p>Usuário</p>
                    <input type="text" name="username" required autocomplete="off" placeholder="Administrador...">
                </label>
                <label class="form-input-text">
                    <p>Senha</p>
                    <input type="password" name="password" required autocomplete="off" placeholder="********">
                </label>
                <button class="form-button" type="submit">Entrar</button>
            </form>
        </div>
    </body>
</html>
