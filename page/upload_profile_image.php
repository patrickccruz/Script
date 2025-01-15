<?php
session_start();
include '../db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: user-login.php");
    exit;
}

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
    $userId = $_SESSION['user']['id'];
    $uploadDir = '../uploads/';
    $uploadFile = $uploadDir . basename($_FILES['profile_image']['name']);
    $uploadFileName = basename($_FILES['profile_image']['name']);

    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $uploadFileName, $userId);
            $stmt->execute();
            $stmt->close();
            $_SESSION['update_success'] = "Imagem de perfil atualizada com sucesso.";
        } else {
            $_SESSION['update_success'] = "Erro ao atualizar a imagem de perfil: " . $conn->error;
        }
    } else {
        $_SESSION['update_success'] = "Erro ao fazer upload da imagem.";
    }
} else {
    $_SESSION['update_success'] = "Nenhuma imagem selecionada ou erro no upload.";
}

header("Location: profile.php");

exit;
?>
