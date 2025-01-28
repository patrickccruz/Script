<?php
session_start();
if(isset($_SESSION['user'])) {
    echo "ID do usuário: " . $_SESSION['user']['id'] . "<br>";
    echo "Nome: " . $_SESSION['user']['name'] . "<br>";
    echo "Username: " . $_SESSION['user']['username'] . "<br>";
    
    // Conectar ao banco de dados
    $conn = new mysqli('localhost', 'root', '', 'sou_digital');
    
    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }
    
    // Verificar status atual do usuário
    $id = $_SESSION['user']['id'];
    $check_sql = "SELECT *, HEX(is_admin) as is_admin_hex FROM users WHERE id = $id";
    $result = $conn->query($check_sql);
    if ($result && $row = $result->fetch_assoc()) {
        echo "<br>Dados do banco de dados:<br>";
        echo "is_admin (valor bruto): " . var_export($row['is_admin'], true) . "<br>";
        echo "is_admin (hexadecimal): " . $row['is_admin_hex'] . "<br>";
    }
    
    echo "<br>Status de administrador na sessão: " . (isset($_SESSION['user']['is_admin']) ? var_export($_SESSION['user']['is_admin'], true) : "Não definido") . "<br>";
    
    // Atualizar o usuário para administrador usando valor 1
    $update_sql = "UPDATE users SET is_admin = 1 WHERE id = $id";
    
    if ($conn->query($update_sql) === TRUE) {
        echo "<br>Usuário atualizado com sucesso para administrador!<br>";
        
        // Atualizar a sessão também
        $_SESSION['user']['is_admin'] = true;
        
        echo "<br>IMPORTANTE: Por favor, faça logout e login novamente para aplicar as alterações.<br>";
        echo "<br><a href='logout.php'>Clique aqui para fazer logout</a>";
    } else {
        echo "Erro ao atualizar usuário: " . $conn->error;
    }
    
    $conn->close();
} else {
    echo "Usuário não está logado";
}
?> 