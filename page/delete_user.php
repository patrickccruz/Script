<?php
include '../db.php';

if (!isset($conn) || !$conn) {
  die("Falha na conexão: " . mysqli_connect_error());
}

$id = $_GET['id'];

$sql = "DELETE FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  header("Location: manage_users.php");
} else {
  echo "Erro ao excluir usuário: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
