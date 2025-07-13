<?php
include("../inc/session.php");
include("database.php");
include("test.string.php");

$perms       = hasPermission($_SESSION["user"]["admin_level"], "inventory");
$permsOrders = hasPermission($_SESSION["user"]["admin_level"], "orders");

$response = ["error" => false];
$action   = $_POST["action"] ?? null;

function crudInventory() {
    global $conn;
    global $action;
    global $perms;

    if ($perms < 2) {
        throw new Exception("Você não possui poder administrativo para essa ação.");
    };
    
    $description = $_POST["description"] ?? null;
    $quantity    = $_POST["quantity"]    ?? null;
    $compatible  = $_POST["compatible"]  ?? "";
    $price       = $_POST["price"]       ?? null;
    $id          = $_POST["id"]          ?? null;

    include('execute.query.php');

    if (!in_array($action, ["del", "new", "edit"])) {
        throw new Exception("Tipo de operação inválida.");
    };

    $queryInsert = "INSERT INTO `inventory` (`description`, `value`, `quantity`, `compatible`) VALUES (?, ?, ?, ?)";
    $queryUpdate = "UPDATE `inventory` SET `description` = ?, `value` = ?, `quantity` = ?, `compatible` = ? WHERE `id` = ?";
    $queryDelete = "DELETE FROM `inventory` WHERE `id` = ?";
    $queryTestId = "SELECT `id` FROM `inventory` WHERE `id` = ?";

    if (in_array($action, ["del", "edit"])) {
        if (!is_numeric($id)) {
            throw new Exception("Campo Id é obrigatório para esta ação.");
        };

        $get = executeQuery($queryTestId, [(int)$id], "i", true);

        if (count($get["records"]) !== 1) {
            throw new Exception("Este registro não existe.");
        };
    };

    if (in_array($action, ["new", "edit"])) {
        if (testIsEmpty($description))               throw new Exception("A descrição do item não pode ser vazia.");
        if (!is_numeric($price) || $price < 0)       throw new Exception("O preço não pode ser abaixo de zero."); 
        if (!is_numeric($quantity) || $quantity < 0) throw new Exception("A Quantidade não pode ser abaixo de zero.");
        if (!isValidMonetary($price))                throw new Exception("O valor de registro não é numérico.");

        $price = adjustValueMonetary($price);

        if ($price == "0.00")                        throw new Exception("O preço não pode ser abaixo de zero.");
  
        $params = [(String)$description, (String)$price, (Int)$quantity, (String)$compatible];
        $types = "sdis";

        if     ($action === "new")  { $query = $queryInsert; }
        elseif ($action === "edit") { $query = $queryUpdate; $types .= "i"; $params[] = (int)$id; };
    } else {
        $params = [(int)$id];
        $types = "i";
        $query = $queryDelete;
    };

    return executeQuery($query, $params, $types);
};

function listInventory() {
    global $conn;
    global $perms;
    global $permsOrders;

    if ($perms < 1 && $permsOrders < 1) {
        throw new Exception("Você não possui permissão para acessar esta informação.");
    };

    $search      = $_POST["search"] ?? "";
    $modelsArray = $_POST["models"] ?? [];
    $limit       = max(1, (int)$_POST["limit"] ?: 20);
    $page        = max(1, (int)$_POST["page"]  ?: 1);
    $offset      = ($page - 1) * $limit;
    $response    = [];
    $stmt        = 
    $countStmt   = null;

    try {
        $conditions = [];
        $params     = [];
        $types      = "";

        if (!empty($modelsArray)) {
            $modelsTerm = "[[:<:]]" . implode("[[:>:]]|[[:<:]]", $modelsArray) . "[[:>:]]";
            $conditions[] = "(`compatible` REGEXP ? OR `compatible` IS NULL)";
            $params[] = $modelsTerm;
            $types .= "s";
        };

        if (!empty($search)) {
            $searchTerm = "%" . removeAccents(urldecode($search)) . "%";
            $conditions[] = "`description` LIKE ?";
            $params[] = $searchTerm;
            $types .= "s";
        };

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $query = "SELECT *, get_model_names_by_ids(`compatible`) AS models FROM `inventory` $whereClause ORDER BY `description` ASC LIMIT ?, ?";
        $countQuery = "SELECT COUNT(id) AS total FROM `inventory` $whereClause";

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
    $response = isset($_POST["action"]) ? crudInventory() : listInventory();
} catch (Exception $e) {
    $response["error"] = "Erro: " . $e->getMessage();
} finally {
    mysqli_close($conn);
};

echo json_encode($response);
exit;
?>
