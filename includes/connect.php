<?php

$DB_HOST = 'localhost';
$DB_NAME = 'churrascaria';
$DB_USER = 'root';
$DB_PASS = '';

try {

    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);


    $connect = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($connect->connect_errno) {
        throw new Exception("MySQLi connect error: " . $connect->connect_error);
    }
} catch (Exception $e) {
    error_log("DB connect error: " . $e->getMessage());
    if (php_sapi_name() !== 'cli') {
        echo "Erro de conexÃ£o com o banco. Verifique logs.";
    }
    exit;
}
