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
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i"
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
      // Log para informar o que está salvo na sessão
      error_log("Dados do usuário na sessão: " . print_r($user, true));
    } else {
      $user = ['id' => 0, 'name' => 'Usuário', 'username' => 'username'];
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $dataChamado = $_POST['dataChamado'];
      $numeroChamado = $_POST['numeroChamado'];
      $cliente = $_POST['cliente'];
      $nomeInformante = $_POST['nomeInformante'];
      $quantidadePatrimonios = $_POST['quantidadePatrimonios'];
      $kmInicial = $_POST['kmInicial'];
      $kmFinal = $_POST['kmFinal'];
      $horaChegada = $_POST['horaChegada'];
      $horaSaida = $_POST['horaSaida'];
      $enderecoPartida = $_POST['enderecoPartida'];
      $enderecoChegada = $_POST['enderecoChegada'];
      $informacoesAdicionais = $_POST['informacoesAdicionais'];
      $arquivoPath = '';

      if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }
        $arquivoPath = $uploadDir . basename($_FILES['arquivo']['name']);
        move_uploaded_file($_FILES['arquivo']['tmp_name'], $arquivoPath);
      }

      $conn = new mysqli('localhost', 'root', '', 'sou_digital');
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }

      $stmt = $conn->prepare("INSERT INTO reports (user_id, data_chamado, numero_chamado, cliente, nome_informante, quantidade_patrimonios, km_inicial, km_final, hora_chegada, hora_saida, endereco_partida, endereco_chegada, informacoes_adicionais, arquivo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("issssiiissssss", $user['id'], $dataChamado, $numeroChamado, $cliente, $nomeInformante, $quantidadePatrimonios, $kmInicial, $kmFinal, $horaChegada, $horaSaida, $enderecoPartida, $enderecoChegada, $informacoesAdicionais, $arquivoPath);

      if ($stmt->execute()) {
        echo "Dados salvos com sucesso!";
      } else {
        echo "Erro ao salvar os dados: " . $stmt->error;
      }

      $stmt->close();
      $conn->close();
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
      <a href="./index.php" class="logo d-flex align-items-center">
      <img src="./assets/img/Ico_geral.png" alt="Logo">
        <span class="d-none d-lg-block">Sou + Digital</span>
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

  <?php include_once 'includes/sidebar.php'; ?>

  <main id="main" class="main">
    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
            <div class="pagetitle">
                <h1>Relatorio de Atendimento</h1>
                <nav>
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Inicial</a></li>
                  </ol>
                </nav>
              </div><!-- End Page Title -->
              <form id="scriptForm" enctype="multipart/form-data">
                <!-- Data do Chamado -->
                <div class="form-floating mb-3">
                  <input type="date" class="form-control" id="dataChamado" name="dataChamado" oninput="infoGeral()">
                  <label for="dataChamado">Data do chamado:</label>
                </div>

                <!-- Número do Chamado -->
                <div class="form-floating mb-3">
                  <input type="number" class="form-control" id="numeroChamado" name="numeroChamado" oninput="infoGeral()">
                  <label for="numeroChamado">Número do chamado:</label>
                </div>

                <!-- Cliente -->
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="cliente" name="cliente" oninput="infoGeral()">
                  <label for="cliente">Cliente:</label>
                </div>

                <!-- Nome de quem informou o chamado -->
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="nomeInformante" name="nomeInformante" oninput="infoGeral()">
                  <label for="nomeInformante">Nome de quem informou o chamado:</label>
                </div>


                <!-- Quantidade de Patrimônios Tratados -->
                <div class="form-floating mb-3">
                  <input type="number" class="form-control" id="quantidadePatrimonios" name="quantidadePatrimonios" oninput="infoGeral()">
                  <label for="quantidadePatrimonios">Quantidade de patrimônios tratados:</label>
                </div>

                <!-- KM Inicial e Final -->
                <div class="form-floating mb-3">
                  <input type="number" class="form-control" id="kmInicial" name="kmInicial" oninput="infoGeral()">
                  <label for="kmInicial">KM inicial:</label>
                </div>
                <div class="form-floating mb-3">
                  <input type="number" class="form-control" id="kmFinal" name="kmFinal" oninput="infoGeral()">
                  <label for="kmFinal">KM final:</label>
                </div>

                <!-- Horários de Chegada e Saída -->
                <div class="form-floating mb-3">
                  <input type="time" class="form-control" id="horaChegada" name="horaChegada" oninput="infoGeral()">
                  <label for="horaChegada">Horário de chegada no chamado:</label>
                </div>
                <div class="form-floating mb-3">
                  <input type="time" class="form-control" id="horaSaida" name="horaSaida" oninput="infoGeral()">
                  <label for="horaSaida">Horário de saída do chamado:</label>
                </div>

                <!-- Endereços -->
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="enderecoPartida" name="enderecoPartida" oninput="infoGeral()">
                  <label for="enderecoPartida">Endereço de partida:</label>
                </div>
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="enderecoChegada" name="enderecoChegada" oninput="infoGeral()">
                  <label for="enderecoChegada">Endereço de chegada:</label>
                </div>

                <!-- Informações Adicionais -->
                <div class="form-floating mb-3">
                  <textarea class="form-control" id="informacoesAdicionais" name="informacoesAdicionais" style="height: 250px"
                    oninput="infoGeral()"></textarea>
                  <label for="informacoesAdicionais">Breve descrição do chamado:</label>
                </div>

                <!-- Upload de Arquivo -->
                <div class="form-floating mb-3">
                  <input type="file" class="form-control" id="arquivo" name="arquivo" accept=".pdf">
                  <label for="arquivo">Anexar Rat (PDF):</label>
                </div>

                <!-- Ações -->
                <div class="mb-3">
                  <button type="button" class="btn btn-outline-primary" id="salvarTudo">Salvar e Enviar</button>
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
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
  // Função para mostrar modal de sucesso
  function mostrarSucesso(mensagem) {
      document.getElementById('mensagemSucesso').textContent = mensagem;
      const modal = new bootstrap.Modal(document.getElementById('sucessoModal'));
      modal.show();
  }

  // Função para mostrar modal de erro
  function mostrarErro(mensagem) {
      document.getElementById('mensagemErro').textContent = mensagem;
      const modal = new bootstrap.Modal(document.getElementById('erroModal'));
      modal.show();
  }

  // Evento do botão Salvar no Banco
  document.getElementById('salvarBanco').addEventListener('click', function(e) {
      e.preventDefault();
      const formData = new FormData(document.getElementById('scriptForm'));
      
      fetch('salvar_dados.php', {
          method: 'POST',
          body: formData
      })
      .then(response => response.text())
      .then(data => {
          if(data.includes("sucesso")) {
              mostrarSucesso("Dados salvos com sucesso no banco de dados!");
          } else {
              mostrarErro("Erro ao salvar os dados: " + data);
          }
      })
      .catch(error => {
          console.error('Erro:', error);
          mostrarErro("Erro ao salvar os dados. Por favor, tente novamente.");
      });
  });

  // Evento do botão Enviar para Discord
  document.getElementById('enviarDiscord').addEventListener('click', function(e) {
      e.preventDefault();
      const formData = JSON.parse(localStorage.getItem("formData"));
      if (formData) {
          enviarParaDiscord()
              .then(() => {
                  mostrarSucesso("Dados enviados com sucesso para o Discord!");
              })
              .catch(error => {
                  mostrarErro("Erro ao enviar para o Discord: " + error.message);
              });
      } else {
          mostrarErro("Nenhum dado encontrado para enviar ao Discord.");
      }
  });
  </script>

  <!-- Modal de Sucesso -->
  <div class="modal fade" id="sucessoModal" tabindex="-1" aria-labelledby="sucessoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="sucessoModalLabel">Sucesso!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p id="mensagemSucesso"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Erro -->
  <div class="modal fade" id="erroModal" tabindex="-1" aria-labelledby="erroModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="erroModalLabel">Erro!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p id="mensagemErro"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

</body>

</html>