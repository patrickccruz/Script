<?php
// Configurações
define('BACKUP_DIR', __DIR__ . '/backups/files/');
define('MAX_BACKUPS', 7); // Manter apenas os últimos 7 backups
define('LOG_FILE', __DIR__ . '/backups/logs/backup.log');

// Diretórios e arquivos a serem excluídos do backup
$excludeDirs = array(
    'backups',
    'uploads/temp',
    'node_modules',
    'vendor'
);

// Função para logging
function writeLog($message) {
    $date = date('Y-m-d H:i:s');
    file_put_contents(LOG_FILE, "[$date] $message\n", FILE_APPEND);
}

// Função recursiva para adicionar arquivos ao ZIP
function addFilesToZip($zip, $sourcePath, $excludeDirs, $localPath = '') {
    $dirHandle = opendir($sourcePath);
    
    while (($file = readdir($dirHandle)) !== false) {
        if ($file != '.' && $file != '..') {
            $filePath = $sourcePath . '/' . $file;
            $localFilePath = $localPath . ($localPath ? '/' : '') . $file;
            
            // Verificar se o diretório deve ser excluído
            if (is_dir($filePath)) {
                if (!in_array($file, $excludeDirs)) {
                    // Criar diretório no ZIP
                    $zip->addEmptyDir($localFilePath);
                    // Recursivamente adicionar conteúdo do diretório
                    addFilesToZip($zip, $filePath, $excludeDirs, $localFilePath);
                }
            } else {
                // Adicionar arquivo ao ZIP
                $zip->addFile($filePath, $localFilePath);
            }
        }
    }
    
    closedir($dirHandle);
}

try {
    // Criar diretório de backup se não existir
    if (!file_exists(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 0755, true);
    }

    // Nome do arquivo de backup
    $backupFile = BACKUP_DIR . 'backup_files_' . date('Y-m-d_H-i-s') . '.zip';

    // Criar arquivo ZIP
    $zip = new ZipArchive();
    if ($zip->open($backupFile, ZipArchive::CREATE) !== TRUE) {
        throw new Exception("Não foi possível criar o arquivo ZIP");
    }

    // Adicionar arquivos ao ZIP
    addFilesToZip($zip, __DIR__, $excludeDirs);

    // Fechar o arquivo ZIP
    $zip->close();

    writeLog("Backup dos arquivos criado com sucesso: " . basename($backupFile));

    // Limpar backups antigos
    $files = glob(BACKUP_DIR . '*.zip');
    if (count($files) > MAX_BACKUPS) {
        // Ordenar por data (mais antigo primeiro)
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // Remover backups excedentes
        $numToDelete = count($files) - MAX_BACKUPS;
        for ($i = 0; $i < $numToDelete; $i++) {
            unlink($files[$i]);
            writeLog("Backup antigo removido: " . basename($files[$i]));
        }
    }

} catch (Exception $e) {
    writeLog("ERRO: " . $e->getMessage());
    exit(1);
}
?> 