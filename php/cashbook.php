<?php
include("../inc/session.php");
include("database.php");
include("test.string.php");

$response = ["error" => false];
$action   = $_POST["action"] ?? null; // String

function getCashBalance() {
    global $conn;
    global $date;

    $query = "SELECT get_cash_balance() AS balance, get_month_balance(?, ?) AS month_balance";
    $response = [
        "month_balance" => "", 
        "total_balance" => ""
    ];

    $date  = strtotime($date);
    $month = date("m", $date);
    $year  = date("Y", $date);
    $stmt  = null;

    try {
        if (!$stmt = mysqli_prepare($conn, $query)) {
            throw new Exception(mysqli_error($conn));
        };

        mysqli_stmt_bind_param($stmt, "ii", $month, $year);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Falha ao executar a consulta: " . mysqli_error($conn));
        };

        if (!$result = mysqli_stmt_get_result($stmt)) {
            throw new Exception("Falha ao obter resultados: " . mysqli_error($conn));
        };

        if ($row = mysqli_fetch_assoc($result)) {
            if (isset($row["balance"])) {
                $response["total_balance"] = $row["balance"];
            };

            if (isset($row["month_balance"])) {
                $response["month_balance"] = $row["month_balance"];
            };
        };

    } catch (Exception $e) {
        throw new Exception("Execução da consulta - " . $e->getMessage());
    } finally {
        if ($stmt) {
            mysqli_stmt_close($stmt);
        };
    };

    return $response;
};

function listCashRecordsByDate() {
    global $conn;
    global $date;

    $query = "SELECT * FROM `cash_book` WHERE DATE(`transaction_date`) = ? ORDER BY `id` ASC";
    $response = ["records" => []];
    $stmt = null;

    try {
        if (!$stmt = mysqli_prepare($conn, $query)) {
            throw new Exception(mysqli_error($conn));
        };

        mysqli_stmt_bind_param($stmt, "s", $date);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Falha ao executar a consulta: " . mysqli_error($conn));
        };

        if (!$result = mysqli_stmt_get_result($stmt)) {
            throw new Exception("Falha ao obter resultados: " . mysqli_error($conn));
        };

        $response["records"] = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Execução da consulta - " . $e->getMessage());
    } finally {
        if ($stmt) mysqli_stmt_close($stmt);
    };

    return $response;
};

function crudCashBook() {
    global $conn;
    global $action;

    $transactionType = $_POST["transaction_type"] ?? null; // String
    $transactionDate = $_POST["transaction_date"] ?? null; // yyyy-MM-dd
    $description     = $_POST["description"]      ?? null; // String
    $amount          = $_POST["amount"]           ?? null; // Float
    $id              = $_POST["id"]               ?? null; // Int

    include('execute.query.php');

    if (!in_array($action, ["del", "new", "edit"])) {
        throw new Exception("Tipo de operação inválida.");
    };

    $queryInsert = "INSERT INTO `cash_book` (`user_id`, `description`, `amount`, `transaction_date`) VALUES (?, ?, ?, ?)";
    $queryUpdate = "UPDATE `cash_book` SET `user_id` = ?, `description` = ?, `amount` = ?, `transaction_date` = ? WHERE `id` = ?";
    $queryDelete = "DELETE FROM `cash_book` WHERE `id` = ?";
    $queryTestId = "SELECT `id` FROM `cash_book` WHERE `id` = ?";

    if (in_array($action, ["del", "edit"])) {
        if (!is_numeric($id)) {
            throw new Exception("Campo Id é obrigatório para esta ação.");
        };

        $get = executeQuery($queryTestId, [(Int)$id], "i", true);
    
        if(@count($get["records"]) !== 1) {
            throw new Exception("Este registro não existe.");
        };
    };

    if (in_array($action, ["new", "edit"])) {

        if (!is_numeric($amount) || $amount < 0) {
            throw new Exception("O valor de registro não pode ser menor que zero.");
        };

        if (testIsEmpty($description)) {
            throw new Exception("A descrição não pode ser vazia.");
        };

        if (!testIsDate($transactionDate)) {
            throw new Exception("A data não é válida.");
        };

        if (!in_array($transactionType, ["in", "out"])) {
            throw new Exception("O tipo de transação precisa ser entrada ou saída.");
        };

        if ($transactionType === "out") {
            $amount = -$amount;
        };

        $params = [(Int)$_SESSION["user"]["id"], (String)$description, (Float)$amount, (String)$transactionDate];
        $types = "isds";

        if     ($action === "new")  { $query = $queryInsert; }
        elseif ($action === "edit") { $query = $queryUpdate; $types .= "i"; $params[] = (int)$id; };
    } else {
        $params = [(int)$id];
        $types = "i";
        $query = $queryDelete;
    };

    return executeQuery($query, $params, $types);
};

try {
    if (isset($_POST["action"])) { 
        $response = crudCashBook();
    } elseif (isset($_POST["date"])) {
        $date = $_POST["date"];

        if(!testIsDate($date)) {
            throw new Exception("Não é uma data válida.");
        };

        $response = array_merge(
            listCashRecordsByDate(),
            getCashBalance()
        );
    };
} catch (Exception $e) {
    $response["error"] = "Erro: " . $e->getMessage();
} finally {
    mysqli_close($conn);
};

echo json_encode($response);
exit;
?>
