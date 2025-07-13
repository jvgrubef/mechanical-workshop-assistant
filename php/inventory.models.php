<?php
include("../inc/session.php");
include("database.php");
include("test.string.php");

$perms       = hasPermission($_SESSION["user"]["admin_level"], "inventory");
$permsOrders = hasPermission($_SESSION["user"]["admin_level"], "orders");

$response = ["error" => false];
$action   = $_POST["action"] ?? null;

function crudModels() {
    global $conn;
    global $action;
    global $perms;

    $name   = $_POST["name"]   ?? null;
    $type   = $_POST["type"]   ?? null;
    $brand  = $_POST["brand"]  ?? null;
    $id     = $_POST["id"]     ?? null;

    include('execute.query.php');

    if (!in_array($action, ["del", "new", "edit", "get"])) {
        throw new Exception("Tipo de operação inválida.");
    };

    if ($action == "get" && $perms < 1) {
        throw new Exception("Você não possui permissão para acessar esta informação.");
    };

    if (in_array($action, ["del", "edit", "new"]) && $perms < 2) {
        throw new Exception("Você não possui poder administrativo para essa ação.");
    };

    $queryInsert = "INSERT INTO `models` (`name`, `type`, `brand`) VALUES (?, ?, ?)";
    $queryUpdate = "UPDATE `models` SET `name` = ?, `type` = ?, `brand` = ? WHERE `id` = ?";
    $queryDelete = "DELETE FROM `models` WHERE `id` = ?";
    $querySelect = "SELECT `id`, `name` FROM `models` WHERE id IN";
    $queryTestId = "SELECT `id` FROM `models` WHERE `id` = ?";

    if (in_array($action, ["del", "edit"])) {
        if (!is_numeric($id)) {
            throw new Exception("Campo Id é obrigatório para esta ação.");
        };
    } elseif ($action === "get") {
        if (!is_array($id)) {
            throw new Exception("Campo Id é obrigatório para esta ação.");
        };

        if (array_filter($id, fn($i) => !is_numeric($i))) {
            throw new Exception("Todos os itens do Id devem ser numéricos.");
        };
    };
    
    if (in_array($action, ["del", "edit"]) ) {
        $getUser = executeQuery($queryTestId, [(int)$id], "i", true);
    
        if(count($getUser["records"]) !== 1) {
            throw new Exception("Este registro não existe.");
        };
    };

    if (in_array($action, ["new", "edit"])) {
        if (testIsEmpty($name))  throw new Exception("A descrição do modelo não pode ser vazio.");
        if (testIsEmpty($type))  throw new Exception("O tipo do modelo não pode ser vazio.");
        if (testIsEmpty($brand)) throw new Exception("A marca do modelo não pode ser vazio.");
        
        $params = [$name, $type, $brand];
        $types = "sss";

        if     ($action === "new")  { $query = $queryInsert; }
        elseif ($action === "edit") { $query = $queryUpdate; $types .= "i"; $params[] = (int)$id; };
    };

    if (in_array($action, ["get", "del"])) { 
        if ($action === "get") {
            $params = array_map('intval', $id);
            $types = str_repeat("i", count($params));
            $query = $querySelect . " (" .implode(",", array_fill(0, count($params), "?")) . ")";
        }
        elseif ($action === "del") {
            $params = [(int)$id];
            $types = "i";
            $query = $queryDelete; 
        };
    };

    return executeQuery($query, $params, $types, ($action === "get"));
};

function listModels() {
    global $conn;
    global $perms;
    global $permsOrders;

    if ($perms < 1 && $permsOrders < 1) {
        throw new Exception("Você não possui permissão para acessar esta informação.");
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
        $types  = "";
        $conditions = "1";
        
        if (!empty($search)) {
            $conditions = "tools_remove_accents(CONCAT(`type`, ' ', `brand`, ' ', `name`)) REGEXP ?";
            $searchTerm = implode('', array_map(fn($term) => "(?=.*$term)", explode(' ', trim(escapeForRegex(removeAccents($search))))));
            $params[] = $searchTerm;
            $types .= "s";
        };

        $query = "SELECT * FROM `models` WHERE $conditions ORDER BY `name` ASC LIMIT ?, ?";
        $countQuery = "SELECT COUNT(*) AS total FROM `models` WHERE $conditions";

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
};

try {
    $response = isset($_POST["action"]) ? crudModels() : listModels();
} catch (Exception $e) {
    $response["error"] = "Erro: " . $e->getMessage();
} finally {
    mysqli_close($conn);
};

echo json_encode($response);
exit;
?>