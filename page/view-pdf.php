<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: autenticacao.php");
    exit;
}

if (!isset($_GET['file']) || empty($_GET['file'])) {
    die('Arquivo não especificado');
}

$file = $_GET['file'];
// Remover qualquer tentativa de directory traversal
$file = str_replace(['../', '..\\'], '', $file);

// Caminho base para os uploads
$basePath = dirname(__DIR__) . '/';
$filePath = $basePath . $file;

// Verificar se o arquivo existe
if (!file_exists($filePath)) {
    die('Arquivo não encontrado');
}

// Verificar se é realmente um PDF
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

if ($mimeType !== 'application/pdf') {
    die('Tipo de arquivo inválido');
}

// Configurar headers para exibir o PDF no navegador
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
header('Cache-Control: public, max-age=0');
header('Content-Length: ' . filesize($filePath));
header('Accept-Ranges: bytes');

// Enviar o arquivo
readfile($filePath); 