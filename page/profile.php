<?php
session_start();
require_once '../db.php';

// Verificação de autenticação
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: user-login.php");
    exit;
}

// Anti CSRF token
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Inicialização de variáveis
$updateMessage = '';
$passwordMessage = '';
$user = ['name' => 'Usuário', 'username' => 'username', 'email' => 'email@example.com'];

try {
    // Buscar dados do usuário
    if (isset($_SESSION['user']['id'])) {
        $userId = filter_var($_SESSION['user']['id'], FILTER_VALIDATE_INT);
        if ($userId === false) {
            throw new Exception("ID de usuário inválido");
        }

        $stmt = $conn->prepare("SELECT name, username, email, profile_image FROM users WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Erro na preparação da consulta: " . $conn->error);
        }

        $stmt->bind_param("i", $userId);
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user) {
            throw new Exception("Usuário não encontrado");
        }

        $stmt->close();
    }

    // Processar mensagens de sessão
    if (isset($_SESSION['update_success'])) {
        $updateMessage = $_SESSION['update_success'];
        unset($_SESSION['update_success']);
    }

    if (isset($_SESSION['password_success'])) {
        $passwordMessage = $_SESSION['password_success'];
        unset($_SESSION['password_success']);
    } elseif (isset($_SESSION['password_error'])) {
        $passwordMessage = $_SESSION['password_error'];
        unset($_SESSION['password_error']);
    }

} catch (Exception $e) {
    error_log("Erro no perfil do usuário: " . $e->getMessage());
    $errorMessage = "Ocorreu um erro ao carregar o perfil. Por favor, tente novamente mais tarde.";
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

  <title>Perfil - Sou + Digital</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Headers de segurança -->
  <meta http-equiv="X-Frame-Options" content="DENY">
  <meta http-equiv="X-Content-Type-Options" content="nosniff">
  <meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src 'self' data: https:; style-src 'self' https: 'unsafe-inline'; script-src 'self' https: 'unsafe-inline';">

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
  <link rel="stylesheet" href="https://unpkg.com/cropperjs/dist/cropper.css">
</head>

<body>

  <?php if (isset($errorMessage)): ?>
  <div class="alert alert-danger" role="alert">
    <?php echo h($errorMessage); ?>
  </div>
  <?php endif; ?>

  <!-- Modal de Alerta -->
  <?php if ($updateMessage): ?>
  <div class="modal fade show" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true" style="display: block;">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="updateModalLabel">Atualização de Perfil</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php echo h($updateMessage); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Modal de Alerta -->
  <?php if ($passwordMessage): ?>
  <div class="modal fade show" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true" style="display: block;">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="passwordModalLabel">Alteração de Senha</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php echo h($passwordMessage); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

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
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo htmlspecialchars($user['name']); ?></span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo htmlspecialchars($user['name']); ?></h6>
              <span><?php echo htmlspecialchars($user['username']); ?></span>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="profile.php">
                <i class="bi bi-person"></i>
                <span>Meu Perfil</span>
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sair</span>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
  </header>

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
      <h1>Perfil</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Inicial</a></li>
          <li class="breadcrumb-item">Usuario</li>
          <li class="breadcrumb-item active">Perfil</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section profile">
      <div class="row">
        <div class="col-xl-4">

          <div class="card">
            <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
              <img src="<?php echo h($user['profile_image'] ? '../uploads/' . $user['profile_image'] : '../assets/img/sem_foto.png'); ?>" alt="Profile">
              <h2><?php echo h($user['name']); ?></h2>
            </div>
          </div>

        </div>

        <div class="col-xl-8">

          <div class="card">
            <div class="card-body pt-3">
              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered">

                <li class="nav-item">
                  <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Geral</button>
                </li>

                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edite perfil </button>
                </li>

                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Alterar senha</button>
                </li>

              </ul>
              <div class="tab-content pt-2">

                <div class="tab-pane fade show active profile-overview" id="profile-overview">

                  <h5 class="card-title">Detalhes do perfil</h5>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label ">Nome completo</div>
                    <div class="col-lg-9 col-md-8"><?php echo h($user['name']); ?></div>
                  </div>


                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Email</div>
                    <div class="col-lg-9 col-md-8"><?php echo h($user['email']); ?></div>
                  </div>

                </div>

                <div class="tab-pane fade profile-edit pt-3" id="profile-edit">

                  <!-- Profile Edit Form -->
                  <!-- Formulário para upload de imagem -->
                  <form action="upload_profile_image.php" method="POST" enctype="multipart/form-data" class="mb-3">
                    <div class="row mb-3">
                      <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Imagem de perfil</label>
                      <div class="col-md-8 col-lg-9">
                        <img id="profileImagePreview" src="<?php echo h($user['profile_image'] ? '../uploads/' . $user['profile_image'] : '../assets/img/sem_foto.png'); ?>" alt="Profile">
                        <div class="pt-2">
                          <input type="file" name="profile_image" class="form-control d-none" id="profileImageInput">
                          <button type="button" onclick="document.getElementById('profileImageInput').click();" class="btn btn-primary btn-sm" title="Upload new profile image"><i class="bi bi-upload"></i></button>
                          <button type="submit" id="submitImage" class="btn btn-success btn-sm d-none" title="Save profile image"><i class="bi bi-check"></i></button>
                          <a href="remove_profile_image.php" class="btn btn-danger btn-sm" title="Remove my profile image"><i class="bi bi-trash"></i></a>
                        </div>
                      </div>
                    </div>
                  </form>

                  <!-- Formulário para outras informações do perfil -->
                  <form action="update_profile.php" method="POST">
                    <div class="row mb-3">
                      <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Nome Completo</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="name" type="text" class="form-control" id="fullName" value="<?php echo h($user['name']); ?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="company" class="col-md-4 col-lg-3 col-form-label">Empresa</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="company" type="text" class="form-control" id="company" value="Sou + Tecnologia" disabled>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="email" type="email" class="form-control" id="Email" value="<?php echo h($user['email']); ?>">
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                  </form><!-- End Profile Edit Form -->

                </div>

                <div class="tab-pane fade pt-3" id="profile-change-password">
                  <!-- Change Password Form -->
                  <form action="change_password.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="row mb-3">
                      <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Senha Atual</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="current_password" type="password" class="form-control" id="currentPassword" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">Nova Senha</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="new_password" type="password" class="form-control" id="newPassword" required minlength="8">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Repita Nova Senha</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="renew_password" type="password" class="form-control" id="renewPassword" required minlength="8">
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="btn btn-primary">Trocar Senha</button>
                    </div>
                  </form><!-- End Change Password Form -->

                </div>

              </div><!-- End Bordered Tabs -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>Sou + Digital</span></strong>. Todos os direitos reservados
    </div>
    <div class="credits">
      Desenvolvido por <a href="https://www.linkedin.com/in/patrick-da-costa-cruz-08493212a/" target="_blank">Patrick C Cruz</a>
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../assets/vendor/echarts/echarts.min.js"></script>
  <script src="../assets/vendor/quill/quill.min.js"></script>
  <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="../assets/js/main.js"></script>

  <script>
    <?php if ($updateMessage): ?>
    var updateModal = new bootstrap.Modal(document.getElementById('updateModal'));
    updateModal.show();
    <?php endif; ?>

    <?php if ($passwordMessage): ?>
    var passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
    passwordModal.show();
    <?php endif; ?>

    // Script para gerenciar o upload de imagem
    document.getElementById('profileImageInput').addEventListener('change', function() {
      if (this.files && this.files[0]) {
        // Mostra o botão de salvar quando uma imagem é selecionada
        document.getElementById('submitImage').classList.remove('d-none');
        
        // Preview da imagem
        var reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('profileImagePreview').src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
      }
    });

    document.addEventListener('DOMContentLoaded', function() {
      // Fechar modais automaticamente após 5 segundos
      setTimeout(function() {
        var modals = document.querySelectorAll('.modal.show');
        modals.forEach(function(modal) {
          var modalInstance = bootstrap.Modal.getInstance(modal);
          if (modalInstance) {
            modalInstance.hide();
          }
        });
      }, 5000);
    });
  </script>

</body>
</html>