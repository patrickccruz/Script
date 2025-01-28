<?php
require_once 'db.php';

// Configurações
define('BACKUP_DIR', __DIR__ . '/backups/database/');
define('MAX_BACKUPS', 7); // Manter apenas os últimos 7 backups
define('LOG_FILE', __DIR__ . '/backups/logs/backup.log');

// Função para logging
function writeLog($message) {
    $date = date('Y-m-d H:i:s');
    file_put_contents(LOG_FILE, "[$date] $message\n", FILE_APPEND);
}

try {
    // Criar diretório de backup se não existir
    if (!file_exists(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 0755, true);
    }

    // Nome do arquivo de backup
    $backupFile = BACKUP_DIR . 'backup_' . date('Y-m-d_H-i-s') . '.sql';

    // Configurações do MySQL (do arquivo db.php)
    $host = $conn->host_info;
    preg_match('/([^:]+)(?::(\d+))?/', $host, $matches);
    $mysqlHost = $matches[1] ?? 'localhost';
    
    // Comando para mysqldump
    $command = sprintf(
        'mysqldump --host=%s --user=%s --password=%s %s > %s',
        escapeshellarg($mysqlHost),
        escapeshellarg('root'), // Substitua pelo seu usuário do MySQL
        escapeshellarg(''), // Substitua pela sua senha do MySQL
        escapeshellarg('script'), // Nome do banco de dados
        escapeshellarg($backupFile)
    );

    // Executar backup
    system($command, $returnValue);

    if ($returnValue !== 0) {
        throw new Exception("Erro ao executar mysqldump");
    }

    // Comprimir o arquivo SQL
    $zip = new ZipArchive();
    $zipFile = $backupFile . '.zip';
    
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        $zip->addFile($backupFile, basename($backupFile));
        $zip->close();
        
        // Remover arquivo SQL original após compressão
        unlink($backupFile);
        
        writeLog("Backup do banco de dados criado com sucesso: " . basename($zipFile));
    } else {
        throw new Exception("Erro ao criar arquivo ZIP");
    }

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