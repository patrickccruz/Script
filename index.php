<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Gerador de Script</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/Icon geral.png" rel="icon">
  <link href="assets/img/Icon geral.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">
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
        header("Location: ./page/user-login.php");
        exit;
    }
    // Supondo que os dados do usuário estejam armazenados na sessão
    if (isset($_SESSION['user'])) {
      $user = $_SESSION['user'];
  } else {
      $user = ['name' => 'Usuário', 'username' => 'username'];
  }
  ?>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/Icon geral.png" alt="">
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
              <a class="dropdown-item d-flex align-items-center" href="./page/profile.php">
                <i class="bi bi-person"></i>
                <span>Meu Perfil</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

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
        <a class="nav-link " href="./index.php">
          <i class="bi bi-grid"></i>
          <span>Gerador Script</span>
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

              <form id="scriptForm">
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

                <!-- Cliente -->
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="cliente" oninput="infoGeral()">
                  <label for="cliente">Cliente:</label>
                </div>

                <!-- Nome de quem informou o chamado -->
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="nomeInformante" oninput="infoGeral()">
                  <label for="nomeInformante">Nome de quem informou o chamado:</label>
                </div>


                <!-- Quantidade de Patrimônios Tratados -->
                <div class="form-floating mb-3">
                  <input type="number" class="form-control" id="quantidadePatrimonios" oninput="infoGeral()">
                  <label for="quantidadePatrimonios">Quantidade de patrimônios tratados:</label>
                </div>

                <!-- KM Inicial e Final -->
                <div class="form-floating mb-3">
                  <input type="number" class="form-control" id="kmInicial" oninput="infoGeral()">
                  <label for="kmInicial">KM inicial:</label>
                </div>
                <div class="form-floating mb-3">
                  <input type="number" class="form-control" id="kmFinal" oninput="infoGeral()">
                  <label for="kmFinal">KM final:</label>
                </div>

                <!-- Horários de Chegada e Saída -->
                <div class="form-floating mb-3">
                  <input type="time" class="form-control" id="horaChegada" oninput="infoGeral()">
                  <label for="horaChegada">Horário de chegada no chamado:</label>
                </div>
                <div class="form-floating mb-3">
                  <input type="time" class="form-control" id="horaSaida" oninput="infoGeral()">
                  <label for="horaSaida">Horário de saída do chamado:</label>
                </div>

                <!-- Endereços -->
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="enderecoPartida" oninput="infoGeral()">
                  <label for="enderecoPartida">Endereço de partida:</label>
                </div>
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="enderecoChegada" oninput="infoGeral()">
                  <label for="enderecoChegada">Endereço de chegada:</label>
                </div>

                <!-- Informações Adicionais -->
                <div class="form-floating mb-3">
                  <textarea class="form-control" id="informacoesAdicionais" style="height: 250px"
                    oninput="infoGeral()"></textarea>
                  <label for="informacoesAdicionais">Breve descrição do chamado:</label>
                </div>

                <!-- Ações -->
                <div class="mb-3">
                  <button type="button" class="btn btn-outline-primary" onclick="copResp2()" data-bs-toggle="modal"
                    data-bs-target="#exampleModal3">Copiar Texto</button>
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
                      <div class="alert alert-warning" role="alert">Ola</div>
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
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>