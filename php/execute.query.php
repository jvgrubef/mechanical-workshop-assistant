<?php
function executeQuery(string $query, array $params, string $types, bool $get = false): array {
    global $conn;
    global $action;

    $stmt = null;
    $response = ["error" => false];
    $params = array_map(fn($param) => is_null($param) ? null : trim($param), $params);

    try {
        if (!$stmt = mysqli_prepare($conn, $query)) {
            throw new Exception("Falha ao preparar a consulta: " . mysqli_error($conn));
        };

        mysqli_stmt_bind_param($stmt, $types, ...$params);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Falha ao executar a consulta: " . mysqli_error($conn));
        };

        if ($get) {
            if (!$result = mysqli_stmt_get_result($stmt)) {
                throw new Exception("Falha ao obter resultados: " . mysqli_error($conn));
            };

            $response["records"] = mysqli_fetch_all($result, MYSQLI_ASSOC);
        };

        if ($action == "new") {
            $response["last"] = mysqli_insert_id($conn);
        };
    } catch (Exception $e) {
        throw new Exception("Execução da consulta - " . $e->getMessage());
    } finally {
        if ($stmt) mysqli_stmt_close($stmt);
    };

    return $response;
};
?>