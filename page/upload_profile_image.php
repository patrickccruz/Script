<?php
session_start();
include '../db.php';

// Criar arquivo de log
$logFile = '../uploads/upload_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Iniciando upload\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Log do conteúdo de $_FILES
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - FILES: " . print_r($_FILES, true) . "\n", FILE_APPEND);

    // Verificar se o diretório uploads existe e tem permissões corretas
    $uploadFileDir = '../uploads/';
    if (!file_exists($uploadFileDir)) {
        mkdir($uploadFileDir, 0777, true);
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Diretório uploads criado\n", FILE_APPEND);
    }

    // Verificar permissões do diretório
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Permissões do diretório: " . substr(sprintf('%o', fileperms($uploadFileDir)), -4) . "\n", FILE_APPEND);

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = time() . '_' . $_FILES['profile_image']['name']; // Adiciona timestamp para evitar nomes duplicados
        $fileSize = $_FILES['profile_image']['size'];
        $fileType = $_FILES['profile_image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Log dos detalhes do arquivo
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Detalhes do arquivo:\n", FILE_APPEND);
        file_put_contents($logFile, "Nome temporário: $fileTmpPath\n", FILE_APPEND);
        file_put_contents($logFile, "Nome final: $fileName\n", FILE_APPEND);
        file_put_contents($logFile, "Tamanho: $fileSize\n", FILE_APPEND);
        file_put_contents($logFile, "Tipo: $fileType\n", FILE_APPEND);

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $dest_path = $uploadFileDir . $fileName;

            try {
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Arquivo movido com sucesso\n", FILE_APPEND);

                    // Verificar se o usuário está logado e tem ID
                    if (isset($_SESSION['user']['id'])) {
                        $userId = $_SESSION['user']['id'];
                        
                        // Log do ID do usuário
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " - ID do usuário: $userId\n", FILE_APPEND);

                        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                        if ($stmt) {
                            $stmt->bind_param("si", $fileName, $userId);
                            $result = $stmt->execute();
                            
                            if ($result) {
                                $_SESSION['update_success'] = "Imagem de perfil atualizada com sucesso.";
                                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Banco de dados atualizado com sucesso\n", FILE_APPEND);
                            } else {
                                $_SESSION['update_success'] = "Erro ao atualizar o banco de dados: " . $stmt->error;
                                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Erro ao atualizar o banco de dados: " . $stmt->error . "\n", FILE_APPEND);
                            }
                            $stmt->close();
                        } else {
                            $_SESSION['update_success'] = "Erro na preparação da consulta: " . $conn->error;
                            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Erro na preparação da consulta: " . $conn->error . "\n", FILE_APPEND);
                        }
                    } else {
                        $_SESSION['update_success'] = "Usuário não está logado corretamente.";
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Usuário não está logado\n", FILE_APPEND);
                    }
                } else {
                    $_SESSION['update_success'] = "Erro ao mover o arquivo para o diretório de uploads.";
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Erro ao mover o arquivo\n", FILE_APPEND);
                }
            } catch (Exception $e) {
                $_SESSION['update_success'] = "Erro: " . $e->getMessage();
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Exceção: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        } else {
            $_SESSION['update_success'] = "Extensão de arquivo não permitida.";
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Extensão não permitida\n", FILE_APPEND);
        }
    } else {
        $uploadError = isset($_FILES['profile_image']) ? $_FILES['profile_image']['error'] : 'Arquivo não enviado';
        $_SESSION['update_success'] = "Nenhuma imagem selecionada ou erro no upload. Código: " . $uploadError;
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Erro no upload: " . $uploadError . "\n", FILE_APPEND);
    }
    
    header("Location: profile.php");
    exit;
}
?>
