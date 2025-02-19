<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Reembolso - Sou + Digital</title>
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
  <style>
    :focus {
      border-color: #1bd81b !important;
      box-shadow: 0 0 5px rgb(7, 228, 25) !important;
      outline: none !important;
    }
  </style>
</head>

<body>
  <?php
    session_start();
    require_once '../includes/upload_functions.php';

    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
        header("Location: autenticacao.php");
        exit;
    }

    $is_page = true; // Indica que estamos em uma página dentro do diretório 'page'
    include_once '../includes/header.php';

    if (isset($_SESSION['user'])) {
      $user = $_SESSION['user'];
      error_log("Dados do usuário na sessão: " . print_r($user, true));
    } else {
      $user = ['id' => 0, 'name' => 'Usuário', 'username' => 'username'];
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $conn = new mysqli('localhost', 'root', '', 'sou_digital');
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }

      try {
        $dataChamado = $_POST['dataChamado'];
        $numeroChamado = $_POST['numeroChamado'];
        $informacoesAdicionais = $_POST['informacoesAdicionais'];

        // Primeiro inserir o reembolso para obter o ID
        $stmt = $conn->prepare("INSERT INTO reembolsos (user_id, data_chamado, numero_chamado, informacoes_adicionais) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user['id'], $dataChamado, $numeroChamado, $informacoesAdicionais);

        if (!$stmt->execute()) {
          throw new Exception("Erro ao salvar reembolso: " . $stmt->error);
        }

        $reembolso_id = $conn->insert_id;
        $arquivoPaths = array();

        // Processamento de múltiplos arquivos
        if (isset($_FILES['arquivos'])) {
          $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/avi', 'video/quicktime'];
          $upload_path = get_upload_path('reimbursement', ['reimbursement_id' => $reembolso_id]);
          
          $fileCount = count($_FILES['arquivos']['name']);
          
          for($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['arquivos']['error'][$i] == UPLOAD_ERR_OK) {
              // Verificar tipo do arquivo
              $finfo = new finfo(FILEINFO_MIME_TYPE);
              $mime_type = $finfo->file($_FILES['arquivos']['tmp_name'][$i]);
              
              if (!is_allowed_file_type($mime_type, $allowed_types)) {
                continue; // Pula arquivos não permitidos
              }

              // Gerar nome único para o arquivo
              $new_filename = generate_unique_filename($_FILES['arquivos']['name'][$i], 'reembolso_');
              $full_path = $upload_path . '/' . $new_filename;
              
              if (move_uploaded_file_safe($_FILES['arquivos']['tmp_name'][$i], $full_path)) {
                $arquivoPaths[] = str_replace('../', '', $full_path);
              }
            }
          }
        }

        // Atualizar reembolso com os caminhos dos arquivos
        if (!empty($arquivoPaths)) {
          $arquivo_path = implode(',', $arquivoPaths);
          $stmt = $conn->prepare("UPDATE reembolsos SET arquivo_path = ? WHERE id = ?");
          $stmt->bind_param("si", $arquivo_path, $reembolso_id);
          
          if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar caminhos dos arquivos: " . $stmt->error);
          }
        }

        $_SESSION['success'] = "Reembolso solicitado com sucesso!";
        header("Location: meus-reembolsos.php");
        exit;

      } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
      }
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
                <h1>Solicitação de Reembolso</h1>
                <nav>
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                    <li class="breadcrumb-item active">Reembolso</li>
                  </ol>
                </nav>
              </div>

              <form id="scriptForm" enctype="multipart/form-data" method="POST">
                <!-- Data do Chamado -->
                <div class="form-floating mb-3">
                  <input type="date" class="form-control" id="dataChamado" name="dataChamado" required>
                  <label for="dataChamado">Data do(s) chamado(s):</label>
                </div>

                <!-- Número do Chamado -->
                <div class="form-floating mb-3">
                  <input type="number" class="form-control" id="numeroChamado" name="numeroChamado" required>
                  <label for="numeroChamado">Número do chamado a que se refere o reembolso (informar todos os chamados, caso tenha mais de um no pedido):</label>
                </div>

                <!-- Upload de Arquivo -->
                <div class="form-floating mb-3">
                  <input type="file" class="form-control" id="arquivo" name="arquivos[]" accept=".pdf,image/*,video/*" multiple>
                  <label for="arquivo">Anexar Arquivos (PDF, Imagem, Vídeo):</label>
                </div>

                <!-- Ações -->
                <div class="mb-3">
                  <button type="submit" class="btn btn-outline-primary" id="salvarTudo">Solicitar e salvar</button>
                  <button type="button" class="btn btn-outline-primary" id="enviarDiscord">Enviar para Discord</button>
                  <button type="button" class="btn btn-outline-danger" onclick="limparFormulario()">Apagar Tudo</button>
                </div>
              </form>

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

  <script>
  // Armazenar o nome e o ID do usuário logado na sessionStorage
  sessionStorage.setItem('nomeUsuario', '<?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>');
  sessionStorage.setItem('idUsuario', '<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>');

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

  // Função para limpar o formulário
  function limparFormulario() {
      document.getElementById('scriptForm').reset();
      mostrarSucesso('Formulário limpo com sucesso!');
  }

  // Função para enviar dados para o Discord
  async function enviarParaDiscord(dados) {
      const webhookUrl = 'https://discord.com/api/webhooks/1333406850187526184/vOEWFHFRY-I8Vs7A5M3CD71REU6fr60vChk_J7-C8-8eUM4DUnm2kMahjvLfajkpR3Xm';
      
      // Criar o FormData para enviar arquivos
      const formData = new FormData();
      
      // Adicionar a mensagem como um campo JSON
      const mensagem = {
          content: 'Nova solicitação de reembolso',
          embeds: [{
              title: `Reembolso - Chamado #${dados.numeroChamado}`,
              color: 0x00ff00,
              fields: [
                  {
                      name: 'Data do Chamado',
                      value: dados.dataChamado,
                      inline: true
                  },
                  {
                      name: 'Número do Chamado',
                      value: dados.numeroChamado,
                      inline: true
                  },
                  {
                      name: 'Descrição',
                      value: dados.informacoesAdicionais || 'Sem descrição',
                      inline: false
                  }
              ],
              timestamp: new Date().toISOString()
          }]
      };

      // Adicionar os arquivos primeiro
      const fileInput = document.getElementById('arquivo');
      const files = fileInput.files;
      
      // Se não houver arquivos, envia apenas a mensagem
      if (files.length === 0) {
          const response = await fetch(webhookUrl, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json'
              },
              body: JSON.stringify(mensagem)
          });

          if (!response.ok) {
              const errorText = await response.text();
              throw new Error(`Erro ao enviar mensagem para o Discord: ${errorText}`);
          }
          return response;
      }

      // Se houver arquivos, envia com FormData
      formData.append('payload_json', JSON.stringify(mensagem));
      
      // Adiciona cada arquivo com o nome 'files[n]'
      for (let i = 0; i < files.length; i++) {
          formData.append(`files[${i}]`, files[i]);
      }

      try {
          const response = await fetch(webhookUrl, {
              method: 'POST',
              body: formData
          });

          if (!response.ok) {
              const errorText = await response.text();
              throw new Error(`Erro ao enviar mensagem para o Discord: ${errorText}`);
          }

          return response;
      } catch (error) {
          console.error('Erro ao enviar para o Discord:', error);
          throw error;
      }
  }

  // Evento do botão Enviar para Discord
  document.getElementById('enviarDiscord').addEventListener('click', function(e) {
      e.preventDefault();
      const formData = new FormData(document.getElementById('scriptForm'));
      const dados = {
          dataChamado: formData.get('dataChamado'),
          numeroChamado: formData.get('numeroChamado'),
          informacoesAdicionais: formData.get('informacoesAdicionais')
      };

      enviarParaDiscord(dados)
          .then(() => {
              mostrarSucesso("Dados e arquivos enviados com sucesso para o Discord!");
          })
          .catch(error => {
              mostrarErro(error.message);
          });
  });

  // Manipular o envio do formulário
  document.getElementById('scriptForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      fetch(window.location.href, {
          method: 'POST',
          body: formData
      })
      .then(response => response.text())
      .then(data => {
          if(data.includes("sucesso")) {
              mostrarSucesso("Dados salvos com sucesso no banco de dados!");
              this.reset(); // Limpa o formulário
          } else {
              mostrarErro("Erro ao salvar os dados: " + data);
          }
      })
      .catch(error => {
          console.error('Erro:', error);
          mostrarErro("Erro ao salvar os dados. Por favor, tente novamente.");
      });
  });
  </script>
</body>

</html>
