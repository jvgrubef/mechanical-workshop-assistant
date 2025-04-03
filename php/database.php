<?php
include('config.php');

$conn = mysqli_connect(
    $db["host"], 
    $db["username"], 
    $db["password"], 
    $db["database"]
);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
};

if (!mysqli_set_charset($conn, "utf8")) {
    die("Error setting charset to utf8: " . mysqli_error($conn));
};
?>
