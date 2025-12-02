<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "churrascaria";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexÃ£o: " . $conn->connect_error);
}



?>