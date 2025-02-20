<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Relatórios - Sou + Digital</title>
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
</head>

<body>
  <?php
    session_start();
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
        header("Location: ../page/autenticacao.php");
        exit;
    }

    // Verificação de permissão de administrador
    if (!isset($_SESSION['user']['is_admin']) || $_SESSION['user']['is_admin'] !== true) {
        header('Location: ../index.php');
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
  ?>

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
                <h1>Relatórios de Atendimento</h1>
                <nav>
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                    <li class="breadcrumb-item active">Relatórios</li>
                  </ol>
                </nav>
              </div>
              <form method="GET">
                <div class="row">
                  <div class="col">
                    <input type="date" name="data_chamado" class="form-control" placeholder="Data do Chamado">
                  </div>
                  <div class="col">
                    <input type="text" name="cliente" class="form-control" placeholder="Cliente">
                  </div>
                  <div class="col">
                    <select name="user_id" class="form-control">
                      <option value="">Todos os Usuários</option>
                      <?php 
                        $users = $conn->query("SELECT id, name FROM users");
                        while ($user = $users->fetch_assoc()): 
                      ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="col">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                  </div>
                </div>
              </form>
              <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php
                  $where = [];
                  if (!empty($_GET['data_chamado'])) {
                    $where[] = "reports.data_chamado = '{$_GET['data_chamado']}'";
                  }
                  if (!empty($_GET['cliente'])) {  
                    $where[] = "reports.cliente LIKE '%{$_GET['cliente']}%'";
                  }
                  if (!empty($_GET['user_id'])) {
                    $where[] = "reports.user_id = {$_GET['user_id']}";
                  }
                  $whereSQL = implode(' AND ', $where);
                  $result = $conn->query("SELECT reports.*, users.name as user_name, users.profile_image FROM reports JOIN users ON reports.user_id = users.id ".($whereSQL ? "WHERE $whereSQL" : "")." ORDER BY reports.data_chamado DESC");
                ?>
                <?php if ($result && $result->num_rows > 0): ?>
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
                            Relatório de <?php echo htmlspecialchars($row['user_name']); ?>
                          </h5>
                          <p class="card-text"><strong>Data do Chamado:</strong> <?php echo date('d/m/Y', strtotime($row['data_chamado'])); ?></p>
                          <p class="card-text"><strong>Número do Chamado:</strong> <?php echo htmlspecialchars($row['numero_chamado']); ?></p>
                          <p class="card-text"><strong>Cliente:</strong> <?php echo htmlspecialchars($row['cliente']); ?></p>
                          <p class="card-text"><strong>Nome Informante:</strong> <?php echo htmlspecialchars($row['nome_informante']); ?></p>
                          <p class="card-text"><strong>Quantidade Patrimônios:</strong> <?php echo htmlspecialchars($row['quantidade_patrimonios']); ?></p>
                          <p class="card-text"><strong>KM Inicial:</strong> <?php echo htmlspecialchars($row['km_inicial']); ?></p>
                          <p class="card-text"><strong>KM Final:</strong> <?php echo htmlspecialchars($row['km_final']); ?></p>
                          <p class="card-text"><strong>Hora Chegada:</strong> <?php echo htmlspecialchars($row['hora_chegada']); ?></p>
                          <p class="card-text"><strong>Hora Saída:</strong> <?php echo htmlspecialchars($row['hora_saida']); ?></p>
                          <p class="card-text"><strong>Endereço Partida:</strong> <?php echo htmlspecialchars($row['endereco_partida']); ?></p>
                          <p class="card-text"><strong>Endereço Chegada:</strong> <?php echo htmlspecialchars($row['endereco_chegada']); ?></p>
                          <p class="card-text"><strong>Informações Adicionais:</strong> <?php echo htmlspecialchars($row['informacoes_adicionais']); ?></p>
                          
                          <?php if ($row['arquivo_path']): ?>
                            <a href="view-pdf.php?file=<?php echo htmlspecialchars($row['arquivo_path']); ?>" 
                               class="btn btn-outline-primary mb-2"
                               target="_blank">
                              <i class="bi bi-file-pdf"></i>
                              Ver RAT
                            </a>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  <?php endwhile; ?>
                <?php else: ?>
                  <div class="col-12">
                    <div class="alert alert-info">
                      Nenhum relatório encontrado.
                    </div>
                  </div>
                <?php endif; ?>
              </div>
              <?php
                $result->free();
                $conn->close();
              ?>
              <a href="exportar-relatorios.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success">Exportar para Excel</a>
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
</body>
</html>
