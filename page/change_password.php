<?php
session_start();
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!isset($_SESSION['user']['id'])) {
    die("Usuário não autenticado.");
  }

  $userId = $_SESSION['user']['id'];
  $currentPassword = $_POST['current_password'];
  $newPassword = $_POST['new_password'];
  $renewPassword = $_POST['renew_password'];

  // Verificar se a nova senha e a confirmação são iguais
  if ($newPassword !== $renewPassword) {
    $_SESSION['password_error'] = "As novas senhas não coincidem.";
    header("Location: profile.php");
    exit;
  }

  // Verificar a senha atual
  $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($currentPassword, $user['password'])) {
      // Atualizar a senha no banco de dados
      $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
      $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
      if ($stmt) {
        $stmt->bind_param("si", $newPasswordHash, $userId);
        if ($stmt->execute()) {
          $_SESSION['password_success'] = "Senha alterada com sucesso.";
          header("Location: profile.php");
          exit;
        } else {
          echo "Erro ao atualizar a senha: " . $stmt->error;
        }
        $stmt->close();
      } else {
        echo "Erro na preparação da consulta: " . $conn->error;
      }
    } else {
      $_SESSION['password_error'] = "Senha atual incorreta.";
      header("Location: profile.php");
      exit;
    }
  } else {
    echo "Erro na preparação da consulta: " . $conn->error;
  }
}
?>
