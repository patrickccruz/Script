<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Reembolsos - Sou + Digital</title>
  <meta content="" name="description">
  <meta content="" name="keywords">
  <link href="../assets/img/Icon geral.png" rel="icon">
  <link href="../assets/img/Icon geral.png" rel="apple-touch-icon">
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
  <?php
    session_start();
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
        header("Location: ../page/autenticacao.php");
        exit;
    }
    $conn = new mysqli('localhost', 'root', '', 'sou_digital');
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    // Buscar dados do usuário logado
    $user = [];
    if (isset($_SESSION['user']['id'])) {
        $userId = $_SESSION['user']['id'];
        $stmt = $conn->prepare("SELECT name, username, profile_image FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result_user = $stmt->get_result();
        $user = $result_user->fetch_assoc();
        $stmt->close();
    }

    $result = $conn->query("SELECT reembolsos.*, users.name as user_name, users.profile_image FROM reembolsos JOIN users ON reembolsos.user_id = users.id ORDER BY reembolsos.created_at DESC");
  ?>
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
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo isset($user['name']) ? htmlspecialchars($user['name']) : 'Usuário'; ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo isset($user['name']) ? htmlspecialchars($user['name']) : 'Usuário'; ?></h6>
              <span><?php echo isset($user['username']) ? htmlspecialchars($user['username']) : ''; ?></span>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="meu-perfil.php">
                <i class="bi bi-person"></i>
                <span>Meu Perfil</span>
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="sair.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sair</span>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
  </header>

  <?php include_once '../includes/sidebar.php'; ?>

  <main id="main" class="main">
    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <div class="pagetitle">
                <h1>Solicitações de Reembolso</h1>
                <nav>
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                    <li class="breadcrumb-item active">Reembolsos</li>
                  </ol>
                </nav>
              </div>
              <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php while ($row = $result->fetch_assoc()): ?>
                  <div class="col">
                    <div class="card h-100">
                      <div class="card-body">
                        <?php if ($row['profile_image']): ?>
                          <img src="<?php echo htmlspecialchars($row['profile_image'] ? '../uploads/' . $row['profile_image'] : '../assets/img/sem_foto.png'); ?>" 
                               alt="Profile Image" 
                               class="rounded-circle"
                               style="width: 80px; height: 80px; object-fit: cover; margin-bottom: 15px;">
                        <?php endif; ?>
                        <h5 class="card-title">
                          Solicitação de <?php echo htmlspecialchars($row['user_name']); ?>
                        </h5>
                        <p class="card-text"><strong>Data do Chamado:</strong> <?php echo date('d/m/Y', strtotime($row['data_chamado'])); ?></p>
                        <p class="card-text"><strong>Número do Chamado:</strong> <?php echo htmlspecialchars($row['numero_chamado']); ?></p>
                        <p class="card-text"><strong>Descrição:</strong> <?php echo htmlspecialchars($row['informacoes_adicionais']); ?></p>
                        <p class="card-text"><strong>Data de Criação:</strong> <?php echo date('d/m/Y H:i:s', strtotime($row['created_at'])); ?></p>
                        
                        <?php if ($row['arquivo_path']): ?>
                          <?php 
                            $arquivos = explode(',', $row['arquivo_path']);
                            foreach($arquivos as $arquivo):
                              $nomeArquivo = basename($arquivo);
                              $extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
                              $icone = '';
                              
                              switch(strtolower($extensao)) {
                                case 'pdf':
                                  $icone = 'bi-file-pdf';
                                  break;
                                case 'jpg':
                                case 'jpeg':
                                case 'png':
                                case 'gif':
                                  $icone = 'bi-file-image';
                                  break;
                                case 'mp4':
                                case 'avi':
                                case 'mov':
                                  $icone = 'bi-file-play';
                                  break;
                                default:
                                  $icone = 'bi-file-earmark';
                              }
                              
                              // Ajusta o caminho do arquivo
                              $caminhoArquivo = str_replace('./uploads/', './uploads/', $arquivo);
                          ?>
                            <a href="<?php echo htmlspecialchars($caminhoArquivo); ?>" 
                               target="_blank" 
                               class="btn btn-outline-primary mb-2 me-2">
                              <i class="bi <?php echo $icone; ?>"></i>
                              Ver Arquivo <?php echo htmlspecialchars($nomeArquivo); ?>
                            </a>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endwhile; ?>
              </div>
              <?php
                $result->free();
                $conn->close();
              ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>Sou + Digital</span></strong>. Todos os direitos reservados
    </div>
    <div class="credits">
      Desenvolvido por <a href="https://www.linkedin.com/in/patrick-da-costa-cruz-08493212a/" target="_blank">Patrick C Cruz</a>
    </div>
  </footer>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html> 