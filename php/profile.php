<?php
include("../inc/session.php");
include("database.php");
include("test.string.php");

$stmt     =
$stmtPwd  = null;
$response = ["error" => false];

try {
    $userFirstName   = $_POST["user_first_name"]  ?? null;
    $userLastName    = $_POST["user_last_name"]   ?? null;
    $newPassword     = $_POST["new_password"]     ?? null;
    $confirmPassword = $_POST["confirm_password"] ?? null;
    $currentPassword = $_POST["current_password"] ?? null;

    $data = [$userFirstName, $userLastName];

    if (testMultipleIsEmpty($data)) {
        throw new Exception("Nome e sobrenome requeridos.");
    };

    $query = "SELECT `password_hash` FROM `users` WHERE `id` = ?";

    if (!$stmtPwd = mysqli_prepare($conn, $query)) {
        throw new Exception("Falha ao preparar a consulta: " . mysqli_error($conn));
    };

    mysqli_stmt_bind_param($stmtPwd, "i", $_SESSION["user"]["id"]);

    if (!mysqli_stmt_execute($stmtPwd)) {
        throw new Exception("Falha ao executar a consulta: " . mysqli_error($conn));
    }

    if (!$result = mysqli_stmt_get_result($stmtPwd)) {
        throw new Exception("Error ao obter resultados: " . mysqli_error($conn));
    };

    if (mysqli_num_rows($result) !== 1) {
        throw new Exception("Usuário inválido.");
    };

    $user = mysqli_fetch_assoc($result);

    if (!testIsEmpty($newPassword)) {
        if (strlen($newPassword) < 8) {
            throw new Exception("A nova senha deve ter pelo menos 8 caracteres.");
        };

        $passwordRegex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/";

        if (!preg_match($passwordRegex, $newPassword)) {
            throw new Exception("A senha deve conter letras maiúsculas, minúsculas, números e caracteres especiais.");
        }

        if ($newPassword !== $confirmPassword) {
            throw new Exception("A nova senha não foi confirmada.");
        };

        if (!password_verify($currentPassword, $user["password_hash"])) {
            throw new Exception("Senha atual inválida.");
        };

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $data[] = $newPasswordHash;

        $update_query = "UPDATE `users` SET `first_name` = ?, `last_name` = ?, `password_hash` = ? WHERE `id` = ?";
        $params = "sssi";

    } else {
        $update_query = "UPDATE `users` SET `first_name` = ?, `last_name` = ? WHERE `id` = ?";
        $params = "ssi";
    }

    $data[] = $_SESSION["user"]["id"];

    if (!$stmt = mysqli_prepare($conn, $update_query)) {
        throw new Exception("Falha ao preparar a consulta: " . mysqli_error($conn));
    };

    mysqli_stmt_bind_param($stmt, $params, ...$data);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Falha ao executar a consulta: " . mysqli_error($conn));
    };

} catch (Exception $e) {
    $response["error"] = "Erro na execução: " . $e->getMessage();
} finally {
    if ($stmtPwd) mysqli_stmt_close($stmtPwd);
    if ($stmt)    mysqli_stmt_close($stmt);

    mysqli_close($conn);
};

echo json_encode($response);
exit;
?>