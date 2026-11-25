<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_HOST'];
$db_username = $_ENV['DB_USERNAME'];
$db_password = $_ENV['DB_PASSWORD'];
$dbName = $_ENV['DB_NAME'];

$conn = new mysqli(
    $servername,
    $db_username,
    $db_password,
    $dbName
);

if ($conn->connect_error) {
    die("Database connection failed.");
}
?>
