<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Gerador de Script</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="../assets/img/Icon geral.png" rel="icon">
  <link href="../assets/img/Icon geral.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i"
    rel="stylesheet">

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
  <style>
    :focus {
      border-color: #1bd81b !important; /* Use !important para sobrescrever */
      box-shadow: 0 0 5px rgb(7, 228, 25) !important;
      outline: none !important;
    }
  </style>
</head>

<body>

  <?php
    session_start();
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
        header("Location: ./user-login.php");
        exit;
    }
    // Supondo que os dados do usuário estejam armazenados na sessão
    if (isset($_SESSION['user'])) {
      $user = $_SESSION['user'];
      // Log para informar o que está salvo na sessão
      error_log("Dados do usuário na sessão: " . print_r($user, true));
    } else {
      $user = ['id' => 0, 'name' => 'Usuário', 'username' => 'username'];
    }
  ?>

<script>
  // Armazenar o nome e o ID do usuário logado na sessionStorage
  sessionStorage.setItem('nomeUsuario', '<?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>');
  sessionStorage.setItem('idUsuario', '<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>');
</script>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="../index.php" class="logo d-flex align-items-center">
        <img src="../assets/img/Icon geral.png" alt="">
        <span class="d-none d-lg-block">Script</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">

            <span class="d-none d-md-block dropdown-toggle ps-2">
              <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>
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
              <a class="dropdown-item d-flex align-items-center" href="./profile.php">
                <i class="bi bi-person"></i>
                <span>Meu Perfil</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

              <a class="dropdown-item d-flex align-items-center" href="logout.php">
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
        <a class="nav-link " href="../index.php">
          <i class="bi bi-journal-text"></i>
          <span>Gerador Script</span>
        </a>
        <a class="nav-link " href="reembolso.php">
          <i class="bx bx-money"></i>
          <span>Solicitação de reembolso</span>
        </a>
      </li><!-- End Dashboard Nav -->
    </ul>
  </aside><!-- End Sidebar-->

  <main id="main" class="main">
    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">

              <form id="scriptForm" enctype="multipart/form-data">
                <!-- Data do Chamado -->
                <div class="form-floating mb-3">
                  <input type="date" class="form-control" id="dataChamado" oninput="infoGeral()">
                  <label for="dataChamado">Data do chamado:</label>
                </div>

                <!-- Número do Chamado -->
                <div class="form-floating mb-3">
                  <input type="number" class="form-control" id="numeroChamado" oninput="infoGeral()">
                  <label for="numeroChamado">Número do chamado:</label>
                </div>


                <!-- Informações Adicionais -->
                <div class="form-floating mb-3">
                  <textarea class="form-control" id="informacoesAdicionais" style="height: 250px"
                    oninput="infoGeral()"></textarea>
                  <label for="informacoesAdicionais">Breve descrição do chamado:</label>
                </div>

                <!-- Upload de Arquivo -->
                <div class="form-floating mb-3">
                  <input type="file" class="form-control" id="arquivo" name="arquivos[]" accept=".pdf,image/*,video/*" multiple>
                  <label for="arquivo">Anexar Arquivos (PDF, Imagem, Vídeo):</label>
                </div>

                <!-- Ações -->
                <div class="mb-3">
                  <button type="button" class="btn btn-outline-primary" id="enviarDiscord">Enviar para Discord</button>
                  <button type="button" class="btn btn-outline-danger" onclick="deleteRespGeral()">Apagar Tudo</button>
                </div>
              </form>

              <!-- Modal -->
              <div class="modal fade" id="exampleModal3" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5" id="exampleModalLabel">Texto Copiado</h1>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <p id="geralResp0"></p>
                      <p id="geralResp1"></p>
                      <p id="geralResp2"></p>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Fechar</button>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </section>
  </main>


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

</body>

</html>
