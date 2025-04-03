<?php
include("../inc/session.php");
include("database.php");
include("test.string.php");

$response = ["error" => false];
$action   = $_POST["action"] ?? null; // String

function crudReminders() {
    global $conn;
    global $action;

    $date        = $_POST['date']       ?? null; // yyyy-MM-dd String
    $deadline    = $_POST['deadline']   ?? null; // yyyy-MM-dd String
    $description = $_POST['details']    ?? "";   // String
    $category    = $_POST['importance'] ?? null; // 0-3 INT
    $type        = $_POST['period']     ?? null; // 0-3 INT
    $title       = $_POST['title']      ?? null; // String
    $day         = $_POST['day']        ?? null; // 1-31 INT
    $id          = $_POST['id']         ?? null; // INT

    include('execute.query.php');

    if (!in_array($action, ["get", "del", "new", "edit"])) {
        throw new Exception("Tipo de operação inválida.");
    };

    $queryInsert = "INSERT INTO `reminders`(`title`, `description`, `reminder_type`, `reminder_category`, `reminder_date`, `reminder_deadline`,  `reminder_day`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $queryUpdate = "UPDATE `reminders` SET `title` = ?, `description` = ?, `reminder_type` = ?, `reminder_category` = ?, `reminder_date` = ?, `reminder_deadline` = ?, `reminder_day` = ? WHERE `id` = ?";
    $queryDelete = "DELETE FROM `reminders` WHERE `id` = ?";
    $querySelect = "SELECT * FROM `reminders` WHERE `id` = ?";
    $queryTestId = "SELECT `id` FROM `reminders` WHERE `id` = ?";

    if (in_array($action, ["del", "edit", "get"])) {
        if (!is_numeric($id)) {
            throw new Exception("Campo Id é obrigatório para esta ação.");
        };
    };

    if (in_array($action, ["del", "edit"])) {
        $get = executeQuery($queryTestId, [(Int)$id], "i", true);

        if (@count($get["records"]) !== 1) {
            throw new Exception("Este registro não existe.");
        };
    };

    if (in_array($action, ["new", "edit"])) {
        if (testIsEmpty($title))                                          throw new Exception("O título não pode ser vazio");
        if (!is_numeric($type) || ($type < 0 || $type > 3))               throw new Exception("O período enviado não é válido.");
        if (!is_numeric($category) || ($category < 0 || $category > 3))   throw new Exception("A importância enviado não é válida.");
        if ($type <= 1 && !testIsDate($date))                             throw new Exception("Data incial não é válida.");
        if ($type == 1 && !testIsDate($deadline))                         throw new Exception("Data final não é válida.");
        if ($type == 1 && new DateTime($date) > new DateTime($deadline))  throw new Exception("Data inicial não pode vim depois da final.");
        if ($type == 2 && (!is_numeric($day) || ($day < 1 || $day > 31))) throw new Exception("O dia não é válido.");

        $customizationMap = [
            0 => [(String)$date, null, null],
            1 => [(String)$date, (String)$deadline, null],
            2 => [null, null, (Int)$day],
            3 => [null, null, null],
        ];

        $customization = $customizationMap[$type] ?? [];

        $params = array_merge([(String)$title, (String)$description, (Int)$type, (Int)$category], $customization);
        $types = "ssiissi";

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

function listReminders() {
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
            $conditions = "tools_remove_accents(CONCAT(`title`, ' ', `description`)) REGEXP ?";
            $searchTerm = implode('', array_map(fn($term) => "(?=.*$term)", explode(' ', trim(escapeForRegex(removeAccents($search))))));
            $params[] = $searchTerm;
            $types .= "s";
        };

        $query = "SELECT * FROM `reminders` WHERE $conditions ORDER BY `created_at` ASC LIMIT ?, ?";
        $countQuery = "SELECT COUNT(*) AS total FROM `reminders` WHERE $conditions";

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
    $response = isset($_POST["action"]) ? crudReminders() : listReminders();
} catch (Exception $e) {
    $response["error"] = "Erro: " . $e->getMessage();
} finally {
    mysqli_close($conn);
};

echo json_encode($response);
exit;

?>