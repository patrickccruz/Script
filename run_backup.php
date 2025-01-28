<?php
// Configurações
define('LOG_FILE', __DIR__ . '/backups/logs/backup.log');

// Função para logging
function writeLog($message) {
    $date = date('Y-m-d H:i:s');
    file_put_contents(LOG_FILE, "[$date] $message\n", FILE_APPEND);
}

try {
    writeLog("Iniciando processo de backup...");

    // Executar backup do banco de dados
    writeLog("Iniciando backup do banco de dados...");
    require_once 'backup_database.php';
    writeLog("Backup do banco de dados concluído.");

    // Executar backup dos arquivos
    writeLog("Iniciando backup dos arquivos...");
    require_once 'backup_files.php';
    writeLog("Backup dos arquivos concluído.");

    writeLog("Processo de backup concluído com sucesso.");

} catch (Exception $e) {
    writeLog("ERRO CRÍTICO: " . $e->getMessage());
    exit(1);
}
?> 