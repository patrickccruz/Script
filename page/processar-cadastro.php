<?php
include('../db.php'); // Certifique-se de que o caminho para db.php está correto

// Verifique se a conexão foi bem-sucedida
if (!$conn) {
    die("Falha na conexão: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Hash da senha

    // Verificar se o email ou username já existem
    $check_sql = "SELECT * FROM users WHERE email=? OR username=?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Erro: Email ou Nome de usuário já existem.');</script>";
    } else {
        $sql = "INSERT INTO users (name, email, username, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $username, $password);
        if ($stmt->execute() === TRUE) {
            echo "<script>alert('Usuário criado com sucesso!'); window.location.href='autenticacao.php';</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    $stmt->close();
}
$conn->close();
?>
