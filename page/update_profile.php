<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: user-login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$email = $_POST['email'];

$query = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
$query->bind_param("ssi", $name, $email, $user_id);

if ($query->execute()) {
    header('Location: profile.php');
} else {
    echo "Erro ao atualizar perfil.";
}
?>
