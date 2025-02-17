<?php
session_start();
require_once '../db.php';

// Limpar sessão anterior se existir
if (isset($_SESSION['loggedin'])) {
    session_destroy();
    session_start();
}

// Proteção contra força bruta
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

// Verificar tentativas de login
if ($_SESSION['login_attempts'] >= 5) {
    $time_passed = time() - $_SESSION['last_attempt'];
    if ($time_passed < 300) { // 5 minutos de bloqueio
        die("Muitas tentativas de login. Tente novamente em " . (300 - $time_passed) . " segundos.");
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validação básica
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            throw new Exception("Todos os campos são obrigatórios");
        }

        // Verificar conexão com o banco
        if ($conn->connect_error) {
            throw new Exception("Erro de conexão com o banco de dados");
        }

        // Consulta preparada
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Erro na preparação da consulta");
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // Reset tentativas de login
            $_SESSION['login_attempts'] = 0;
            
            // Configurar sessão
            $_SESSION['loggedin'] = true;
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'is_admin' => (bool)$user['is_admin']
            ];

            // Registrar login bem-sucedido
            $ip = $_SERVER['REMOTE_ADDR'];
            error_log("Login bem-sucedido: {$username} - IP: {$ip}");

            // Redirecionar
            header("Location: autenticacao.php");
            exit;
        } else {
            // Incrementar tentativas
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt'] = time();
            
            throw new Exception("Credenciais inválidas");
        }
    } catch (Exception $e) {
        $_SESSION['login_error'] = $e->getMessage();
        error_log("Tentativa de login falhou: {$username} - " . $e->getMessage());
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }

    header("Location: user-login.php");
    exit;
}
?>
