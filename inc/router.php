<?php
session_start();

$page = $_GET['page'] ?? 'dashboard';

if (!preg_match('/^[a-z0-9_-]+$/i', $page)) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

if ($page === 'login') {
    if (isset($_SESSION['user']['id'])) {
        header("Location: ?page=dashboard");
    } else {
        include('login.php');
    };

    exit;
};

if ($page === 'logout') {
    session_unset();
    session_destroy();
};

if(!isset($_SESSION['user']['id'])){
    header("Location: ?page=login");
    exit;
};

$permissionRequires = [
    "dashboard" => 1,
    "cashbook"  => 2,
    "clients"   => 2,
    "inventory" => 2,
    "orders"    => 2,
    "profile"   => 1,
    "reminders" => 2,
    "users"     => 2,
][$page] ?? null;


if (!$permissionRequires) {
    header('HTTP/1.1 404 Not Found');
    exit;
};

$adminLevel = $_SESSION["user"]["admin_level"];
$permissionsPage = hasPermission($adminLevel , $page);

if ($permissionRequires > 1 &&  $permissionsPage < 1) {
    header('HTTP/1.1 403 Forbidden');
    exit;
};

$pagePath = realpath('./pages/' . basename($page));

if (!$pagePath || strpos($pagePath, realpath('./pages/')) !== 0) {
    header('HTTP/1.1 404 Not Found');
    exit;
};

?>