<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: user-login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$renew_password = $_POST['renew_password'];

// Verifica se as senhas novas coincidem
if ($new_password !== $renew_password) {
    echo "As novas senhas não coincidem.";
    exit();
}

// Busca a senha atual no banco de dados
$query = $conn->prepare("SELECT password FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Verifica se a senha atual está correta
if (!password_verify($current_password, $user['password'])) {
    echo "Senha atual incorreta.";
    exit();
}

// Atualiza a senha no banco de dados
$new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);
$query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$query->bind_param("si", $new_password_hashed, $user_id);

if ($query->execute()) {
    header('Location: profile.php');
} else {
    echo "Erro ao alterar senha.";
}
?>
