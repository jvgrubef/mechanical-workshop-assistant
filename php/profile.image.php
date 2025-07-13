<?php

$action = $_POST["action"] ?? null;
if (!in_array($action, ['upload', 'delete'])) {
    echo json_encode(["error" => "Ação inválida. Por favor, envie uma nova imagem ou exclua a imagem atual."]);
    return;
};

include("../inc/session.php");
include("database.php");

$response     = ["error" => false];
$stmt         = null;
$filePath     = "../img/users/";
$id           = $_SESSION["user"]["id"];
$oldImage     = $_SESSION["user"]["image"];
$fileBasename = "default.jpg";

$removeSentIfFailed = false;

try {    
    if ($action == "delete") {
        if($oldImage == $fileBasename) return;
    } elseif ($action == "upload") {
        if (!isset($_FILES["image"])) {
            throw new Exception("Não foi encontrado o arquivo enviado.");
        };

        $fileName      = md5(time());
        $fileExtension = strtolower(pathinfo(basename($_FILES["image"]["name"]), PATHINFO_EXTENSION));
        $fileTmp       = $_FILES["image"]["tmp_name"];
        $fileBasename  = "user_$id-$fileName.$fileExtension";

        $check = getimagesize($fileTmp);
        if ($check === false) {
            throw new Exception("O arquivo não é uma imagem.");
        };

        if ($_FILES["image"]["size"] > 5000000) {
            throw new Exception("Arquivo é muito grande, limite 5MB.");
        };

        $extensionsAllowed = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($fileExtension, $extensionsAllowed )) {
            throw new Exception("Apenas arquivos " . strtoupper(implode(", ", $extensionsAllowed)) . " são permitidos.");
        };

        if (!move_uploaded_file($fileTmp, $filePath.$fileBasename)) {
            throw new Exception("Ocorreu um erro ao tentar guardar a imagem.");
        };

        $removeSentIfFailed = true;
    };

    $execQuery = "UPDATE `users` SET `image_path` = ? WHERE `id` = ?";

    if (!$stmt = mysqli_prepare($conn, $execQuery)) {
        throw new Exception("Erro ao preparar a consulta: " . mysqli_error($conn));
    };

    mysqli_stmt_bind_param($stmt, "si", $fileBasename, $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao executar a consulta: " . mysqli_error($conn));
    };

    $removeSentIfFailed        = false;
    $response["image"]         = $fileBasename;
    $_SESSION["user"]["image"] = $fileBasename;
} catch (Exception $e) {
    $response["error"] = "Erro na execução: " . $e->getMessage();
} finally {
    if ($stmt) mysqli_stmt_close($stmt);

    $deleteFile = $removeSentIfFailed ? $fileBasename : $oldImage;
    if ($deleteFile != "default.jpg" && file_exists($filePath.$deleteFile)) {
        if(!@unlink($filePath.$deleteFile)){
            $response["warning"] = "A imagem enviada não foi deletada.";
        };
    };

    mysqli_close($conn);
    echo json_encode($response);
    exit;
};
?>