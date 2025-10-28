<?php
    $server = 'localhost';
    $user = 'root';
    $pass = '';
    $db = 'churrascaria';

    $connect = mysqli_connect($server, $user, $pass, $db);

    try {
    $pdo = new PDO("mysql:host=$server;dbname=$db", $user, $pass);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
