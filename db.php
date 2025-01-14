<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sou_digital";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Remova a mensagem de depuração
// echo "Conexão com o banco de dados estabelecida com sucesso.";
?>