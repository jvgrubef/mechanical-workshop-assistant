<?php
include("../inc/session.php");
include("database.php");
include("test.string.php");

$response = ["error" => false];
$action   = $_POST["action"] ?? null; // String

function crudUsers() {
    global $conn;
    global $action;

    $username        = $_POST["username"]         ?? null; // String
    $firstName       = $_POST["first_name"]       ?? null; // String
    $lastName        = $_POST["last_name"]        ?? null; // String
    $adminLevel      = $_POST["admin_level"]      ?? null; // 0-3 INT
    $newPassword     = $_POST["new_password"]     ?? null; // String
    $confirmPassword = $_POST["confirm_password"] ?? null; // String
    $id              = $_POST["id"]               ?? null; // INT

    include("execute.query.php");

    if ($_SESSION["user"]["id"] == $id) {
        throw new Exception("Você não pode alterar sua própria conta, altere pela página de perfil.");
    };

    if (!in_array($action, ["del", "new", "edit"])) {
        throw new Exception("Tipo de operação inválida.");
    };
    
    $queryInsert = "INSERT INTO `users` (`username`, `first_name`, `last_name`, `admin_level`, `password_hash`) VALUES (?, ?, ?, ?, ?)";
    $queryUpdate = "UPDATE `users` SET `username` = ?, `first_name` = ?, `last_name` = ?, `admin_level` = ?";
    $queryDelete = "DELETE FROM `users` WHERE `id` = ?";
    $queryTestId = "SELECT `image_path` FROM `users` WHERE `id` = ?";

    if (in_array($action, ["del", "edit"])) {
        if (!is_numeric($id)) {
            throw new Exception("Campo Id é obrigatório para esta ação.");
        };

        $get = executeQuery($queryTestId, [(Int)$id], "i", true);

        if (@count($get["records"]) !== 1) {
            throw new Exception("Este registro não existe.");
        };
    };   

    if (in_array($action, ["new", "edit"])) {
        if (testIsEmpty($username))  throw new Exception("O usuário não pode ser vazio.");
        if (testIsEmpty($firstName)) throw new Exception("O nome não pode ser vazio.");
        if (testIsEmpty($lastName))  throw new Exception("O sobrenome não pode ser vazio.");

        if (!is_numeric($adminLevel) || ($adminLevel < 0 || $adminLevel > 3)) {
            throw new Exception("Nível adminstrativo informado não é válido.");
        };

        $params = [(String)$username, (String)$firstName, (String)$lastName, (Int)$adminLevel];
        $types = "sssi";

        if ($action === "new" && testIsEmpty($newPassword)) {
            throw new Exception("Senha é obrigatória para novo cadastro.");
        } elseif (!testIsEmpty($newPassword)) {
            $password_regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+?])[A-Za-z\d!@#$%^&*()_+?]{8,}$/";

            if (strlen($newPassword) < 8)                   throw new Exception("A nova senha deve ter pelo menos 8 caracteres.");
            if ($newPassword !== $confirmPassword)          throw new Exception("A nova senha não foi confirmada.");
            if (!preg_match($password_regex, $newPassword)) throw new Exception("A senha deve conter letras maiúsculas, minúsculas, números e caracteres especiais.");

            if ($action === "edit") $queryUpdate .= ", `password_hash` = ?";

            $types .= "s";
            $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
        };

        if ($action === "new") { 
            $query = $queryInsert; 
        }
        elseif ($action === "edit") { 
            $query = $queryUpdate . " WHERE `id` = ?"; 
            $types .= "i"; 
            $params[] = (Int)$id; 
        };
    } else {
        $query = $queryDelete;
        $params = [(int)$id];
        $types = "i";

        if ($get["records"][0]["image_path"] !== "default.jpg") {
            @unlink("../img/users/".$get["records"][0]["image_path"]);
        };
    };

    return executeQuery($query, $params, $types);
};

function listUsers() {
    global $conn;

    $search    = $_POST["search"] ?? "";
    $limit     = isset($_POST["limit"]) ? (int)$_POST["limit"] : 20;
    $page      = isset($_POST["page"]) ? (int)$_POST["page"] : 1;
    $offset    = ($page - 1) * $limit;
    $response  = [];
    $stmt      = 
    $countStmt = null;

    try {
        $params = [];
        $types = "";
        $conditions = "1";
        
        if (!empty($search)) {
            $conditions = "tools_remove_accents(CONCAT(`username`, ' ', `first_name`,' ', `last_name`)) REGEXP ?";
            $searchTerm = implode('', array_map(fn($term) => "(?=.*$term)", explode(' ', trim(escapeForRegex(removeAccents($search))))));
            $params[] = $searchTerm;
            $types .= "s";
        };

        $query = "SELECT * FROM `users` WHERE $conditions ORDER BY CONCAT(`first_name`, ' ', `last_name`) ASC LIMIT ?, ?";
        $countQuery = "SELECT COUNT(*) AS total FROM `users` WHERE $conditions";

        if (!$stmt = mysqli_prepare($conn, $query)) {
            throw new Exception(mysqli_error($conn));
        };
    
        $params[] = $offset;
        $params[] = $limit;
        $types .= "ii";

        mysqli_stmt_bind_param($stmt, $types, ...$params);
    
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_error($conn));
        };

        $result = mysqli_stmt_get_result($stmt);
        $records = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
        if (!$countStmt = mysqli_prepare($conn, $countQuery)) {
            throw new Exception(mysqli_error($conn));
        };

        if (!empty($search)) {
            mysqli_stmt_bind_param($countStmt, "s", $searchTerm);
        };
    
        if (!mysqli_stmt_execute($countStmt)) {
            throw new Exception(mysqli_error($conn));
        };
    
        $countResult  = mysqli_stmt_get_result($countStmt);
        $totalRecords = mysqli_fetch_assoc($countResult)["total"];
        $totalPages   = ceil($totalRecords / $limit);
    
        $response = [
            "records"     => $records,
            "pages"       => [
                "records" => $totalRecords,
                "total"   => $totalPages
            ],
            "currentPage" => $page
        ];

    } catch (Exception $e) {
        throw new Exception("Erro na execução: " . $e->getMessage());
    } finally {
        if ($stmt)      mysqli_stmt_close($stmt);
        if ($countStmt) mysqli_stmt_close($countStmt);
    };
    
    return $response;
}

try {
    if ($_SESSION["user"]["admin_level"] < 2) {
        throw new Exception("Você não possui poder administrativo para essa ação.");
    };

    $response = isset($_POST["action"]) ? crudUsers() : listUsers();
} catch (Exception $e) {
    $response["error"] = "Erro: " . $e->getMessage();
} finally {
    mysqli_close($conn);
};

echo json_encode($response);
exit;
?>
