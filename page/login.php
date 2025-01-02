<?php
session_start();
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Verifique se a conexão com o banco de dados está funcionando
  if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
  }

  // Lógica de autenticação real
  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  if ($stmt) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
      if (password_verify($password, $user['password'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];
        header("Location: ../index.php");
        exit;
      } else {
        $_SESSION['login_error'] = 'Senha inválida.';
      }
    } else {
      $_SESSION['login_error'] = 'Usuário não encontrado.';
    }
    $stmt->close();
  } else {
    echo "Erro na preparação da consulta: " . $conn->error;
  }

  header("Location: user-login.php");
  exit;
}
?>
