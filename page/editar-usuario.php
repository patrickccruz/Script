<?php
session_start();
require_once '../db.php';

// Verificação de autenticação
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: user-login.php');
    exit;
}

// Verificação de permissão de administrador
if (!isset($_SESSION['user']['is_admin']) || $_SESSION['user']['is_admin'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Anti CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token CSRF inválido');
    }
}
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Inicialização de variáveis
$error_message = '';
$success_message = '';
$user = null;

try {
    if (!isset($conn) || !$conn) {
        throw new Exception("Falha na conexão com o banco de dados");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validação e sanitização das entradas
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;

        if ($id === false || empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Dados inválidos fornecidos");
        }

        // Atualizar usuário
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, is_admin=? WHERE id=?");
        if (!$stmt) {
            throw new Exception("Erro na preparação da consulta: " . $conn->error);
        }

        $stmt->bind_param("ssii", $name, $email, $is_admin, $id);
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar usuário: " . $stmt->error);
        }

        // Log da alteração
        error_log("Usuário ID {$id} atualizado por " . $_SESSION['user']['username']);
        
        $_SESSION['success_message'] = "Usuário atualizado com sucesso";
        header("Location: manage_users.php");
        exit;

    } else {
        // Buscar dados do usuário para edição
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            throw new Exception("ID de usuário inválido");
        }

        $stmt = $conn->prepare("SELECT id, name, email, is_admin FROM users WHERE id=?");
        if (!$stmt) {
            throw new Exception("Erro na preparação da consulta: " . $conn->error);
        }

        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Erro ao buscar usuário: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user) {
            throw new Exception("Usuário não encontrado");
        }
    }

} catch (Exception $e) {
    error_log("Erro na edição de usuário: " . $e->getMessage());
    $error_message = $e->getMessage();
} finally {
    // Fechar o stmt apenas se ele estiver definido e não fechado
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}

// Função auxiliar para sanitização de saída
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Sou + Digital - Gerenciar Usuários</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="../assets/img/Icon geral.png" rel="icon">
  <link href="../assets/img/Icon geral.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="../assets/css/style.css" rel="stylesheet">

  <!-- Headers de segurança -->
  <meta http-equiv="X-Frame-Options" content="DENY">
  <meta http-equiv="X-Content-Type-Options" content="nosniff">
  <meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src 'self' data: https:; style-src 'self' https: 'unsafe-inline'; script-src 'self' https: 'unsafe-inline';">

</head>
<body>

<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="../index.php" class="logo d-flex align-items-center">
            <img src="../assets/img/Ico_geral.png" alt="Logo">
            <span class="d-none d-lg-block">Sou + Digital</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>
    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <span class="d-none d-md-block dropdown-toggle ps-2">
                        <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>Nome: <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                        <span> Usuario: <?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="./page/profile.php">
                            <i class="bi bi-person"></i>
                            <span>Meu Perfil</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="page/logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Deslogar</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</header><!-- End Header -->

<?php include_once '../includes/sidebar.php'; ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Editar Usuário</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                <li class="breadcrumb-item">Administração</li>
                <li class="breadcrumb-item active">Editar Usuário</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Formulário de Edição</h5>

                        <?php if ($user): ?>
                            <form method="post" action="edit_user.php" class="needs-validation" novalidate>
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="id" value="<?php echo h($user['id']); ?>">
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo h($user['name']); ?>" required
                                           pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]{2,}" 
                                           title="Nome deve conter apenas letras e espaços">
                                    <div class="invalid-feedback">
                                        Por favor, insira um nome válido.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo h($user['email']); ?>" required>
                                    <div class="invalid-feedback">
                                        Por favor, insira um email válido.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" 
                                               <?php echo ($user['is_admin'] ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_admin">
                                            Usuário Administrador
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Salvar</button>
                                <a href="manage_users.php" class="btn btn-secondary">Cancelar</a>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                Usuário não encontrado.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Scripts -->
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    // Validação do formulário
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>

<!-- ======= Footer ======= -->
<footer id="footer" class="footer">
    <div class="copyright">
        &copy; Copyright <strong><span>Patrick C Cruz</span></strong>. Todos os direitos Reservado
    </div>
    <div class="credits">
        Feito pelo <a href="https://www.linkedin.com/in/patrick-da-costa-cruz-08493212a/" target="_blank">Patrick C Cruz</a>
    </div>
</footer><!-- End Footer -->

  <!-- Vendor JS Files -->
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../script.js"></script>
  <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>

  <!-- Template Main JS File -->
  <script src="../assets/js/main.js"></script>

</body>
</html>
