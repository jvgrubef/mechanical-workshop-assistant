<?php
session_start();

include("database.php");
include("test.string.php");

$stmt     = null;
$response = ["error" => false];

try {
    $username = $_POST["username"] ?? null;
    $password = $_POST["password"] ?? null;

    if (testMultipleIsEmpty([$username, $password])) {
        throw new Exception("Por favor, preencha todos os campos.");
    };

    $query = "SELECT `id`, `username`, `first_name`, `last_name`, `password_hash`, `admin_level`, `image_path` FROM `users` WHERE `username` = ?";

    if (!$stmt = mysqli_prepare($conn, $query)) {
        throw new Exception("Erro ao preparar a consulta: " . mysqli_error($conn));
    };

    mysqli_stmt_bind_param($stmt, "s", $username);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao executar a consulta: " . mysqli_error($conn));
    };

    $result = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($result) !== 1) {
        throw new Exception("Login ou senha inválidos.");
    };

    $user = mysqli_fetch_assoc($result);

    if (!password_verify($password, $user["password_hash"])) {
        throw new Exception("Login ou senha inválidos.");
    };

    $_SESSION["user"] = [
        "id"          => $user["id"],
        "username"    => $user["username"],
        "name"        => [
            "first"   => $user["first_name"],
            "last"    => $user["last_name"]
        ],
        "admin_level" => $user["admin_level"],
        "image"       => $user["image_path"]
    ];

} catch (Exception $e) {
    $response["error"] = "Falha ao logar - " . $e->getMessage();
} finally {
    if ($stmt) mysqli_stmt_close($stmt);
    mysqli_close($conn);
};

echo json_encode($response);
exit;
?>
