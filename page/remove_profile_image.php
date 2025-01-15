<?php
session_start();
include '../db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: user-login.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$stmt = $conn->prepare("UPDATE users SET profile_image = NULL WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    $_SESSION['update_success'] = "Imagem de perfil removida com sucesso.";
} else {
    $_SESSION['update_success'] = "Erro ao remover a imagem de perfil: " . $conn->error;
}

header("Location: profile.php");
exit;
?>
