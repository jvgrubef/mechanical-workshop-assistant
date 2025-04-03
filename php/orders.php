<?php
include("../inc/session.php");
include("database.php");
include("test.string.php");

$response = ["error" => false];
$action   = $_POST["action"] ?? null; // String

function crudOrdes() {
    global $conn;
    global $action;

    $name     = $_POST["name"]      ?? null; // String
    $details  = $_POST["details"]   ?? "";   // String
    $items    = $_POST["items"]     ?? null; // Array
    $values   = $_POST["values"]    ?? null; // Array
    $status   = $_POST["status"]    ?? null; // 0-5 INT
    $date     = $_POST["date"]      ?? null; // yyyy-MM-dd String
    $clientId = $_POST["client_id"] ?? null; // INT
    $id       = $_POST["id"]        ?? null; // INT

    include('execute.query.php');

    if (!in_array($action, ["get", "del", "new", "edit"])) {
        throw new Exception("Tipo de operação inválida.");
    };

    $queryInsert = "INSERT INTO `orders` (`client_id`, `name`, `details`, `items`, `status`, `order_date`) VALUES (?, ?, ?, ?, ?, ?)";
    $queryUpdate = "UPDATE `orders` SET `client_id` = ?, `name` = ?, `details` = ?, `items` = ?, `status` = ?, `order_date` = ? WHERE `id` = ?";
    $queryDelete = "DELETE FROM `orders` WHERE `id` = ?";
    $querySelect = "SELECT  o.id AS `id`, o.name AS `name`, o.items AS `items`, o.status AS `status`, o.details AS `details`, 
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

        if ((!is_array($items) || !is_array($values)) || count($items) !== count($values)) {
            throw new Exception("Os campos dos \"itens\" e seus \"valores\" não são válidos.");
        };

        if (!is_numeric($status) || ($status < 0 || $status > 5)) {
            throw new Exception("O tipo de \"status\" fornecido não é válido.");
        };

        if(!testIsDate($date)) {
            throw new Exception("A data fornecida não é válida.");
        };

        $itemsList = json_encode(array_map(fn($item, $value) => [
            "item" => $item,
            "value" => str_replace(",", ".", preg_replace("/[^0-9,]+/", "", $value))
        ], $items, $values));

        $params = [(Int)$clientId, (String)$name, (String)$details, $itemsList, (Int)$status, (String)$date];
        $types = "isssis";

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

    $search    = $_POST["search"] ?? "";
    $client    = $_POST["client"] ?? "";

    $limit     = isset($_POST["limit"]) ? (int)$_POST["limit"] : 20;
    $page      = isset($_POST["page"])  ? (int)$_POST["page"] : 1;
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
                  INNER JOIN `clients` AS c ON o.client_id = c.id $whereClause ORDER BY o.order_date DESC LIMIT ?, ?";

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