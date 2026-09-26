<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$env = parse_ini_file(__DIR__ . "/../.env");

if (!$env) {
    die("Error parsing .env file");
}

date_default_timezone_set($env["APP_TIMEZONE"] ?? "Asia/Colombo");

$host = $env["DB_HOST"];
$db   = $env["DB_NAME"];
$user = $env["DB_USER"];
$pass = $env["DB_PASSWORD"];
$port = $env["DB_PORT"];

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>