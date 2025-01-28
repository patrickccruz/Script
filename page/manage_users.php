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

// Adicione esta linha para garantir que a variável $user esteja definida
$user = $_SESSION['user'] ?? null;

// Anti CSRF token para operações de modificação
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Inicialização de variáveis
$error_message = '';
$success_message = '';
$users = [];

try {
    if (!isset($conn) || !$conn) {
        throw new Exception("Falha na conexão com o banco de dados");
    }

    // Buscar usuários com prepared statement
    $stmt = $conn->prepare("SELECT id, name, email FROM users ORDER BY name");
    if (!$stmt) {
        throw new Exception("Erro na preparação da consulta: " . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Erro ao buscar usuários: " . $stmt->error);
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    // Processar mensagens de sessão
    if (isset($_SESSION['success_message'])) {
        $success_message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        $error_message = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
    }

} catch (Exception $e) {
    error_log("Erro no gerenciamento de usuários: " . $e->getMessage());
    $error_message = "Ocorreu um erro ao carregar os usuários. Por favor, tente novamente mais tarde.";
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

  <?php
    if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo h($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success" role="alert">
            <?php echo h($success_message); ?>
        </div>
    <?php endif; ?>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="./index.php" class="logo d-flex align-items-center">
      <img src="../assets/img/Ico_geral.png" alt="Logo">
        <span class="d-none d-lg-block">Sou + Digital</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">

            <span class="d-none d-md-block dropdown-toggle ps-2">
              <?php if ($user): ?>
                <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>
              <?php else: ?>
                Usuário não encontrado
              <?php endif; ?>
            </span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6>Nome: <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></h6>
              <span> Usuario: <?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="../page/profile.php">
                <i class="bi bi-person"></i>
                <span>Meu Perfil</span>
              </a>
            </li>
            <li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="page/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Deslogar</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->
  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
      <li class="nav-item">
        <a class="nav-link" href="../index.php">
          <i class="bi bi-journal-text"></i>
          <span>Gerador Script</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="reembolso.php">
          <i class="bx bx-money"></i>
          <span>Solicitação de reembolso</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="view-reembolsos.php">
          <i class="bx bx-list-ul"></i>
          <span>Visualizar Reembolsos</span>
        </a>
      </li>
    </ul>
  </aside>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Gerenciar Usuários</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Inicial</a></li>
          <li class="breadcrumb-item">Administração</li>
          <li class="breadcrumb-item active">Gerenciar Usuários</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">Lista de Usuários</h5>
                <a href="create_user.php" class="btn btn-success">Criar Novo Usuário</a>
              </div>

              <?php if (empty($users)): ?>
                <div class="alert alert-info">
                  Nenhum usuário encontrado.
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Email</th>
                        <th scope="col">Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($users as $user): ?>
                        <tr>
                          <th scope="row"><?php echo h($user['id']); ?></th>
                          <td><?php echo h($user['name']); ?></td>
                          <td><?php echo h($user['email']); ?></td>
                          <td>
                            <div class="btn-group" role="group">
                              <a href="edit_user.php?id=<?php echo h($user['id']); ?>" 
                                 class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil"></i> Editar
                              </a>
                              <form action="delete_user.php" method="post" class="d-inline" 
                                    onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="id" value="<?php echo h($user['id']); ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                  <i class="bi bi-trash"></i> Excluir
                                </button>
                              </form>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->


  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>Patrick C Cruz</span></strong>. Todos os direitos Reservado
    </div>
    <div class="credits">
      Feito pelo <a href="https://www.linkedin.com/in/patrick-da-costa-cruz-08493212a/" target="_blank">Patrick C
        Cruz</a>
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../script.js"></script>
  <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>

  <!-- Template Main JS File -->
  <script src="../assets/js/main.js"></script>

  <script>
    // Fechar alertas automaticamente
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
          alert.style.display = 'none';
        });
      }, 5000);
    });
  </script>

</body>

</html>
