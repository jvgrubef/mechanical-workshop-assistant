<?php
if (!isset($_POST["submit"])) return;

include("../inc/session.php");
include("database.php");

$response = ["error" => false];
$delNew   = false;
$stmt     = null;
$filePath = "../img/users/";

try {
    if (!isset($_FILES["image"])) {
        throw new Exception("Não foi encontrado o arquivo enviado");
    };

    $id            = $_SESSION["user"]["id"];
    $fileName      = md5(time());
    $fileExtension = strtolower(pathinfo(basename($_FILES["image"]["name"]), PATHINFO_EXTENSION));
    $fileTmp       = $_FILES["image"]["tmp_name"];
    $fileBasename  = "user_$id-$fileName.$fileExtension";

    $check = getimagesize($fileTmp);
    if ($check === false) {
        throw new Exception("O arquivo não é uma imagem");
    };

    if ($_FILES["image"]["size"] > 5000000) {
        throw new Exception("Arquivo é muito grande, limite 5MB");
    };

    if (!in_array($fileExtension, ["jpg", "jpeg", "png", "gif"])) {
        throw new Exception("Apenas arquivos JPG, JPEG, PNG e GIF são permitidos");
    };

    if (!move_uploaded_file($fileTmp, $filePath.$fileBasename)) {
        throw new Exception("Ocorreu um erro ao tentar guardar a imagem.");
    }

    $updateQuery = "UPDATE `users` SET `image_path` = ? WHERE `id` = ?";

    if (!$stmt = mysqli_prepare($conn, $updateQuery)) {
        $delNew = true;
        throw new Exception("Erro ao preparar a consulta: " . mysqli_error($conn));
    };

    mysqli_stmt_bind_param($stmt, "si", $fileBasename, $id);

    if (!mysqli_stmt_execute($stmt)) {
        $delNew = true;
        throw new Exception("Erro ao executar a consulta: " . mysqli_error($conn));
    };

    $response = [
        "error" => false, 
        "image" => [
            "new" => $fileBasename,
            "old" => $_SESSION["user"]["image"]
        ]
    ];

    if (isset($_SESSION["user"]["image"]) && $_SESSION["user"]["image"] != "default.jpg") {
        if(!@unlink($filePath . $_SESSION["user"]["image"])){
            $response["warning"] = "A imagem antiga não foi deletada";
        };
    };

    $_SESSION["user"]["image"] = $fileBasename;
} catch (Exception $e) {
    $response["error"] = "Erro na execução: " . $e->getMessage();
} finally {
    if ($stmt) {
        mysqli_stmt_close($stmt);
    };

    if ($delNew) {
        if(!@unlink($filePath.$fileBasename)){
            $response["warning"] = "A imagem enviada não foi deletada";
        };
    };

    mysqli_close($conn);
};

echo json_encode($response);
exit;    

?>
