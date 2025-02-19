<?php
/**
 * Funções auxiliares para gerenciamento de uploads
 */

/**
 * Cria a estrutura de diretórios para um upload
 * @param string $base_path Caminho base (uploads/)
 * @param array $subdirs Array com os subdiretórios a serem criados
 * @return string Caminho completo criado
 */
function create_upload_path($base_path, $subdirs) {
    $current_path = $base_path;
    foreach ($subdirs as $dir) {
        $current_path .= '/' . $dir;
        if (!is_dir($current_path)) {
            mkdir($current_path, 0777, true);
        }
    }
    return $current_path;
}

/**
 * Gera um nome único para o arquivo
 * @param string $original_name Nome original do arquivo
 * @param string $prefix Prefixo opcional para o nome do arquivo
 * @return string Nome único do arquivo
 */
function generate_unique_filename($original_name, $prefix = '') {
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    return $prefix . uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Move um arquivo para o diretório de destino
 * @param string $tmp_name Nome temporário do arquivo
 * @param string $destination Caminho de destino completo
 * @return bool True se o arquivo foi movido com sucesso
 */
function move_uploaded_file_safe($tmp_name, $destination) {
    return move_uploaded_file($tmp_name, $destination);
}

/**
 * Verifica se o tipo de arquivo é permitido
 * @param string $mime_type Tipo MIME do arquivo
 * @param array $allowed_types Array com os tipos permitidos
 * @return bool True se o tipo é permitido
 */
function is_allowed_file_type($mime_type, $allowed_types) {
    return in_array($mime_type, $allowed_types);
}

/**
 * Remove um arquivo e seu diretório pai se estiver vazio
 * @param string $file_path Caminho completo do arquivo
 * @return bool True se o arquivo foi removido com sucesso
 */
function remove_file_and_empty_dir($file_path) {
    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            $dir = dirname($file_path);
            if (is_dir($dir) && count(scandir($dir)) <= 2) { // . e ..
                rmdir($dir);
            }
            return true;
        }
    }
    return false;
}

/**
 * Retorna o caminho relativo para upload baseado no tipo
 * @param string $type Tipo de upload (profile, blog, reimbursement, reports)
 * @param array $params Parâmetros adicionais (user_id, post_id, etc)
 * @return string Caminho relativo para upload
 */
function get_upload_path($type, $params = []) {
    // Determinar o caminho base correto
    $root_dir = $_SERVER['DOCUMENT_ROOT'] . '/Script';
    $base_path = $root_dir . '/uploads';
    
    // Log para debug
    error_log("Root dir: " . $root_dir);
    error_log("Base path: " . $base_path);
    
    // Criar o caminho absoluto
    $absolute_path = '';
    switch ($type) {
        case 'profile':
            $absolute_path = create_upload_path($base_path, ['users', $params['user_id'], 'profile']);
            break;
            
        case 'blog':
            $absolute_path = create_upload_path($base_path, ['blog', $params['post_id']]);
            break;
            
        case 'reimbursement':
            $absolute_path = create_upload_path($base_path, ['reimbursements', $params['reimbursement_id']]);
            break;
            
        case 'documents':
            $absolute_path = create_upload_path($base_path, ['users', $params['user_id'], 'documents']);
            break;
            
        case 'reports':
            $absolute_path = create_upload_path($base_path, ['reports', $params['report_id']]);
            break;
            
        default:
            throw new Exception('Tipo de upload inválido');
    }
    
    error_log("Absolute path: " . $absolute_path);
    return $absolute_path;
} 