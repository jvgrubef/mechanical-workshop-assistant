<?php
include("../inc/session.php");
include("database.php");
include("test.string.php");

$response = ["error" => false];
$action   = $_POST["action"] ?? null; // String

function crudClients() {
    global $conn;
    global $action;

    $name       = $_POST["name"]        ?? null; // String
    $phones     = $_POST["phones"]      ?? "";   // String
    $phonesList = $_POST["phones_list"] ?? "";   // Array
    $rating     = $_POST["rating"]      ?? "3";  // 0-5 INT
    $address    = $_POST["address"]     ?? "";   // String
    $id         = $_POST["id"]          ?? null; // INT

    include('execute.query.php');

    if (!in_array($action, ["del", "new", "edit"])) {
        throw new Exception("Tipo de operação inválida.");
    };

    $queryInsert = "INSERT INTO `clients` (`name`, `phones`, `address`, `rating`) VALUES (?, ?, ?, ?)";
    $queryUpdate = "UPDATE `clients` SET `name` = ?, `phones` = ?, `address` = ?, `rating` = ? WHERE `id` = ?";
    $queryDelete = "DELETE FROM `clients` WHERE `id` = ?";
    $queryTestId = "SELECT `id` FROM `clients` WHERE `id` = ?";

    if (in_array($action, ["del", "edit"])) {
        $getUser = executeQuery($queryTestId, [(int)$id], "i", true);
    
        if(count($getUser["records"]) !== 1) {
            throw new Exception("Este registro não existe.");
        };
    };

    if (in_array($action, ["new", "edit"])) {
        if (testIsEmpty($name))                                throw new Exception("O nome do cliente não pode ser vazio.");
        if (!is_numeric($price) || ($price < 0 || $price > 5)) throw new Exception("A nota do cliente deve ser de zero a cinco.");
        
        $phonesListInsert = [];

        if (!testIsEmpty($phones)) $phonesListInsert[] = $phones;
        if (is_array($phonesList)) $phonesListInsert = array_merge($phonesListInsert, $phonesList);
    
        $phonesListInsert = json_encode($phonesListInsert);

        $params = [(String)$name, $phonesListInsert, (String)$address, (Int)$rating];
        $types = "sssi";

        if     ($action === "new")  { $query = $queryInsert; }
        elseif ($action === "edit") { $query = $queryUpdate; $types .= "i"; $params[] = (int)$id; };
    } else {
        $params = [(int)$id];
        $types = "i";
        $query = $queryDelete;
    };

    return executeQuery($query, $params, $types);
};

function listClients() {
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
        $types  = "";
        $conditions = "1";
        
        if (!empty($search)) {
            $conditions = "tools_remove_accents(CONCAT(`name`, ' ', `phones`, ' ', `address`)) REGEXP ? ";
            $searchTerm = implode('', array_map(fn($term) => "(?=.*$term)", explode(' ', trim(escapeForRegex(removeAccents($search))))));
            $params[] = $searchTerm;
            $types .= "s";
        };

        $query = "SELECT * FROM `clients` WHERE $conditions ORDER BY `name` ASC LIMIT ?, ?";
        $countQuery = "SELECT COUNT(*) AS total FROM `clients` WHERE $conditions";

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
    $response = isset($_POST["action"]) ? crudClients() : listClients();
} catch (Exception $e) {
    $response["error"] = "Erro: " . $e->getMessage();
} finally {
    mysqli_close($conn);
};

echo json_encode($response);
exit;
?>
