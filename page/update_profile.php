<?php
session_start();
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!isset($_SESSION['user']['id'])) {
    die("Usuário não autenticado.");
  }

  $userId = $_SESSION['user']['id'];
  $name = $_POST['name'];
  $email = $_POST['email'];

  // Atualizar as informações do usuário no banco de dados
  $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
  if ($stmt) {
    $stmt->bind_param("ssi", $name, $email, $userId);
    if ($stmt->execute()) {
      // Atualizar as informações na sessão
      $_SESSION['user']['name'] = $name;
      $_SESSION['user']['email'] = $email;
      $_SESSION['update_success'] = "Informações atualizadas com sucesso.";
      echo "<script>alert('Informações atualizadas: Nome - $name, Email - $email');</script>";
      header("Location: profile.php");
      exit;
    } else {
      echo "Erro ao atualizar o perfil: " . $stmt->error;
    }
    $stmt->close();
  } else {
    echo "Erro na preparação da consulta: " . $conn->error;
  }
}
?>
