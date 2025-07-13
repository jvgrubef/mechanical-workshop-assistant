<?php
include("../inc/session.php");
include("database.php");
include("test.string.php");

$perms    = hasPermission($_SESSION["user"]["admin_level"], "users");

$response = ["error" => false];
$action   = $_POST["action"] ?? null;

function getPermissionsFields() {
    $errorItens = [];
    $errorLabel = [
        'cashbook'  => "Livro Caixa",
        'clients'   => "Clientes",
        'orders'    => "Orçamentos",
        'inventory' => "Estoque & Marcas",
        'reminders' => "Lembretes",
        'users'     => "Usuários"
    ];

    $permissionsFields = [
        'cashbook'  => $_POST['cashbook']  ?? 0,
        'clients'   => $_POST['clients']   ?? 0,
        'orders'    => $_POST['orders']    ?? 0,
        'inventory' => $_POST['inventory'] ?? 0,
        'reminders' => $_POST['reminders'] ?? 0,
        'users'     => $_POST['users']     ?? 0,
    ];

    foreach ($permissionsFields as $key => $value) {
        if (!is_numeric($value) || ($value < 0 || $value > 2)) {
            $permissionsFields[$key] = 0;
            $errorItens[] = $errorLabel[$key];
            continue;
        };

        $permissionsFields[$key] = testIsEmpty($value) ?  0 : (Int)$value;
    };

    $error = !empty($errorItens) ? "Valor inválido para " . implode(", ", $errorItens) . ". Por favor, envie um valor inteiro de 0 á 2" : false;

    $bits = 0;

    $bits |= ($permissionsFields["cashbook"]  << 0);  // 0° & 1º bit (cashbook)
    $bits |= ($permissionsFields["clients"]   << 2);  // 2º & 3º bit (clients)
    $bits |= ($permissionsFields["orders"]    << 4);  // 4º & 5º bit (orders)
    $bits |= ($permissionsFields["inventory"] << 6);  // 6º & 7º bit (inventory)
    $bits |= ($permissionsFields["reminders"] << 8);  // 8º & 9º bit (reminders)
    $bits |= ($permissionsFields["users"]     << 10); // 10º & 11º bit (users)

    return [
        "error" => $error, 
        "bits" => $bits
    ];
};

function crudUsers() {
    global $conn;
    global $action;
    global $perms;

    if ($perms < 2) {
        throw new Exception("Você não possui poder administrativo para essa ação.");
    };

    $username        = $_POST["username"]         ?? null;
    $firstName       = $_POST["first_name"]       ?? null;
    $lastName        = $_POST["last_name"]        ?? null;
    $newPassword     = $_POST["new_password"]     ?? null;
    $confirmPassword = $_POST["confirm_password"] ?? null;
    $id              = $_POST["id"]               ?? null;

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

        $permissions = getPermissionsFields();

        if ($permissions["error"])   throw new Exception($permissions["error"]);
        if (testIsEmpty($username))  throw new Exception("O usuário não pode ser vazio.");
        if (testIsEmpty($firstName)) throw new Exception("O nome não pode ser vazio.");
        if (testIsEmpty($lastName))  throw new Exception("O sobrenome não pode ser vazio.");

        $params = [(String)$username, (String)$firstName, (String)$lastName, $permissions["bits"]];
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
    global $perms;

    if ($perms < 1) {
        throw new Exception("Você não possui poder administrativo para essa ação.");
    };

    $search    = $_POST["search"] ?? "";
    $limit     = max(1, (int)$_POST["limit"] ?: 20);
    $page      = max(1, (int)$_POST["page"]  ?: 1);
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
    $response = isset($_POST["action"]) ? crudUsers() : listUsers();
} catch (Exception $e) {
    $response["error"] = "Erro: " . $e->getMessage();
} finally {
    mysqli_close($conn);
};

echo json_encode($response);
exit;
?>
