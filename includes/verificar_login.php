<?php
session_start();

$response = [
    'logado' => isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true
];

header('Content-Type: application/json');
echo json_encode($response);
?>