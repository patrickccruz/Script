<?php
session_start();
require_once '../db.php';

// Verificação de autenticação
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: user-login.php');
    exit;
}

// Configurações de upload
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_DIR', '../uploads/');

// Criar arquivo de log com timestamp
$logFile = '../uploads/upload_log.txt';

function writeLog($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

writeLog("Iniciando upload");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Verificar diretório de uploads
        if (!file_exists(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
            writeLog("Diretório uploads criado");
        }

        if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("Nenhum arquivo enviado");
        }

        $file = $_FILES['profile_image'];
        
        // Validações básicas
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload: " . $file['error']);
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception("Arquivo muito grande. Máximo permitido: " . (MAX_FILE_SIZE / 1024 / 1024) . "MB");
        }

        // Validar tipo de arquivo usando finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception("Tipo de arquivo não permitido");
        }

        // Gerar nome único para o arquivo
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ALLOWED_EXTENSIONS)) {
            throw new Exception("Extensão não permitida");
        }

        $newFileName = uniqid('profile_', true) . '.' . $fileExtension;
        $uploadPath = UPLOAD_DIR . $newFileName;

        // Mover arquivo
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception("Falha ao mover arquivo");
        }

        // Atualizar banco de dados
        if (!isset($_SESSION['user']['id'])) {
            throw new Exception("Usuário não identificado");
        }

        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Erro na preparação da query: " . $conn->error);
        }

        $stmt->bind_param("si", $newFileName, $_SESSION['user']['id']);
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar banco de dados: " . $stmt->error);
        }

        writeLog("Upload bem sucedido: " . $newFileName);
        $_SESSION['update_success'] = "Imagem de perfil atualizada com sucesso.";

    } catch (Exception $e) {
        writeLog("ERRO: " . $e->getMessage());
        $_SESSION['update_success'] = "Erro: " . $e->getMessage();
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }

    header("Location: profile.php");
    exit;
}
?>
