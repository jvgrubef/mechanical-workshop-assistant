<?php
include("../inc/session.php");
include("database.php");
include("test.string.php");

$perms    = hasPermission($_SESSION["user"]["admin_level"], "orders");

$response = ["error" => false];
$action   = $_POST["action"] ?? null;

function crudOrdes() {
    global $conn;
    global $action;
    global $perms;

    $name     = $_POST["name"]      ?? null;
    $details  = $_POST["details"]   ?? "";
    $items    = $_POST["items"]     ?? null;
    $values   = $_POST["values"]    ?? null;
    $quantity = $_POST["qtd"]       ?? null;
    $status   = $_POST["status"]    ?? null;
    $date     = $_POST["date"]      ?? null;
    $clientId = $_POST["client_id"] ?? null;
    $model    = $_POST["model"]     ?? null;
    $id       = $_POST["id"]        ?? null;

    include('execute.query.php');

    if (!in_array($action, ["get", "del", "new", "edit"])) {
        throw new Exception("Tipo de operação inválida.");
    };

    if ($action == "get" && $perms < 1) {
        throw new Exception("Você não possui permissão para acessar esta informação.");
    };

    if (in_array($action, ["del", "edit", "new"]) && $perms < 2) {
        throw new Exception("Você não possui poder administrativo para essa ação.");
    };

    $queryInsert = "INSERT INTO `orders` (`client_id`, `name`, `details`, `items`, `status`, `order_date`, `model`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $queryUpdate = "UPDATE `orders` SET `client_id` = ?, `name` = ?, `details` = ?, `items` = ?, `status` = ?, `order_date` = ?, `model` = ? WHERE `id` = ?";
    $queryDelete = "DELETE FROM `orders` WHERE `id` = ?";
    $querySelect = "SELECT  o.id AS `id`, o.name AS `name`, o.items AS `items`, o.status AS `status`, o.details AS `details`, o.model AS `model`,
                            o.order_date AS `order_date`, c.id  AS `client_id`, c.name AS `client_name`, c.phones AS `client_phones` 
                    FROM `orders` AS o INNER JOIN `clients` AS c  ON o.client_id = c.id WHERE o.id = ?";
    $queryTestId = "SELECT `id` FROM `orders` WHERE `id` = ?";

    if (in_array($action, ["del", "edit", "get"])) {
        if (!is_numeric($id)) {
            throw new Exception("Campo Id é obrigatório para esta ação.");
        };
    };

    if (in_array($action, ["del", "edit"])) {
        $get = executeQuery($queryTestId, [(Int)$id], "i", true);

        if(@count($get["records"]) !== 1) {
            throw new Exception("Este registro não existe.");
        };
    };

    if (in_array($action, ["new", "edit"])) {
        if(!is_numeric($clientId)) {
            throw new Exception("Informe qual é o cliente, por favor.");
        };

        if (testIsEmpty($name)) {
            throw new Exception("Informe o nome do orçamento, por favor.");
        };

        if (!is_array($items) || !is_array($values) || !is_array($quantity)) {
            throw new Exception("Os campos dos \"itens\" e seus \"valores\" não são válidos.");
        };

        if (count($items) !== count($values) || count($values) !== count($quantity)) {
            throw new Exception("Os campos de \"itens\" não tem a mesma quantidade de valores");
        };

        if (!is_numeric($status) || ($status < 0 || $status > 5)) {
            throw new Exception("O tipo de \"status\" fornecido não é válido.");
        };

        if(!testIsDate($date)) {
            throw new Exception("A data fornecida não é válida.");
        };

        $itemsList = json_encode(array_map(fn($item, $value, $quantity) => [
            "item" => $item,
            "quantity" => $quantity,
            "value" => adjustValueMonetary(str_replace(",", ".", preg_replace("/[^0-9,]+/", "", $value)))
        ], $items, $values, $quantity));

        $params = [(Int)$clientId, (String)$name, (String)$details, $itemsList, (Int)$status, (String)$date, (int)$model];
        $types = "isssisi";

        if     ($action === "new")  { $query = $queryInsert; }
        elseif ($action === "edit") { $query = $queryUpdate; $types .= "i"; $params[] = (Int)$id; };
    };

    if (in_array($action, ["get", "del"])) { 
        $params = [(Int)$id];
        $types = "i";

        if     ($action === "get") { $query = $querySelect; }
        elseif ($action === "del") { $query = $queryDelete; };
    };

    return executeQuery($query, $params, $types, ($action === "get"));
};

function listOrders() {
    global $conn;    
    global $perms;
    
    if ($perms < 1) {
        throw new Exception("Você não possui poder administrativo para essa ação.");
    };

    $search    = $_POST["search"] ?? "";
    $client    = $_POST["client"] ?? "";
    $limit     = max(1, (int)$_POST["limit"] ?: 20);
    $page      = max(1, (int)$_POST["page"]  ?: 1);
    $offset    = ($page - 1) * $limit;
    $response  = [];
    $stmt      = 
    $countStmt = null;
    
    try {
        $conditions = [];
        $params = [];
        $types  = "";
        
        if (!empty($search)) {
            $searchTerm = implode('', array_map(fn($term) => "(?=.*$term)", explode(' ', trim(escapeForRegex(removeAccents($search))))));
            $conditions[] = "tools_remove_accents(CONCAT(o.name, ' ', c.name)) REGEXP ?";
            $params[] = $searchTerm;
            $types .= "s";
        };

        if (is_numeric($client)) {
            $clientTerm = (int)$client;
            $conditions[] = "client_id = ?";
            $params[] = $clientTerm;
            $types .= "i";
        };

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $query = "SELECT o.id AS order_id, o.name AS order_name, o.status AS order_status, c.name AS client_name FROM `orders` AS o 
                  INNER JOIN `clients` AS c ON o.client_id = c.id $whereClause ORDER BY o.order_date DESC, o.id DESC LIMIT ?, ?";

        $countQuery = "SELECT COUNT(o.id) AS `total` FROM `orders` AS o INNER JOIN `clients` AS c ON o.client_id = c.id $whereClause";

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
    
        $countParams = array_slice($params, 0, -2);
        $countTypes  = substr($types, 0, -2);

        if (!empty($countParams)) {
            mysqli_stmt_bind_param($countStmt, $countTypes, ...$countParams);
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
    $response = isset($_POST["action"]) ? crudOrdes() : listOrders();
} catch (Exception $e) {
    $response["error"] = "Erro: " . $e->getMessage();
} finally {
    mysqli_close($conn);
};

echo json_encode($response);
exit;
?>