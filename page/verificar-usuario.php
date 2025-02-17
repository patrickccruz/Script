<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validação básica
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Por favor, preencha todos os campos.";
        header("Location: autenticacao.php");
        exit;
    }

    // Conexão com o banco de dados
    $conn = new mysqli('localhost', 'root', '', 'sou_digital');
    if ($conn->connect_error) {
        $_SESSION['login_error'] = "Erro de conexão com o banco de dados.";
        header("Location: autenticacao.php");
        exit;
    }

    // Preparar e executar a consulta
    $stmt = $conn->prepare("SELECT id, name, username, password, is_admin FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verificar a senha
        if (password_verify($password, $user['password'])) {
            // Login bem-sucedido
            $_SESSION['loggedin'] = true;
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'is_admin' => (bool)$user['is_admin']
            ];

            if ($remember) {
                // Configurar cookie para 30 dias
                setcookie("remember_user", $user['username'], time() + (86400 * 30), "/");
            }

            header("Location: ../index.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Senha incorreta.";
        }
    } else {
        $_SESSION['login_error'] = "Usuário não encontrado.";
    }

    $stmt->close();
    $conn->close();

    header("Location: autenticacao.php");
    exit;
} else {
    // Se alguém tentar acessar este arquivo diretamente
    header("Location: autenticacao.php");
    exit;
}
?> 