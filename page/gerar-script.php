<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: autenticacao.php");
    exit;
}

require_once '../includes/upload_functions.php';
$is_page = true;

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'sou_digital');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = '';
$success_message = '';

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
} else {
    $user = ['id' => 0, 'name' => 'Usuário', 'username' => 'username'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
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

        // Primeiro inserir o registro para obter o ID
        $stmt = $conn->prepare("INSERT INTO reports (user_id, data_chamado, numero_chamado, cliente, nome_informante, quantidade_patrimonios, km_inicial, km_final, hora_chegada, hora_saida, endereco_partida, endereco_chegada, informacoes_adicionais) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssiiiissss", $user['id'], $dataChamado, $numeroChamado, $cliente, $nomeInformante, $quantidadePatrimonios, $kmInicial, $kmFinal, $horaChegada, $horaSaida, $enderecoPartida, $enderecoChegada, $informacoesAdicionais);

        if (!$stmt->execute()) {
            throw new Exception("Erro ao salvar os dados: " . $stmt->error);
        }

        $report_id = $conn->insert_id;

        // Processar upload do arquivo
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($_FILES['arquivo']['tmp_name']);
            
            if (!is_allowed_file_type($mime_type, $allowed_types)) {
                throw new Exception("Tipo de arquivo não permitido. Use apenas PDF.");
            }

            // Gerar nome único e mover arquivo
            $new_filename = generate_unique_filename($_FILES['arquivo']['name'], 'rat_');
            $upload_path = get_upload_path('reports', ['report_id' => $report_id]);
            $full_path = $upload_path . '/' . $new_filename;

            error_log("Tentando fazer upload para: " . $full_path);
            
            if (move_uploaded_file_safe($_FILES['arquivo']['tmp_name'], $full_path)) {
                error_log("Upload realizado com sucesso para: " . $full_path);
                // Armazenar apenas o caminho relativo no banco
                $arquivoPath = 'uploads/reports/' . $report_id . '/' . $new_filename;
                
                // Atualizar o registro com o caminho do arquivo
                $stmt = $conn->prepare("UPDATE reports SET arquivo_path = ? WHERE id = ?");
                $stmt->bind_param("si", $arquivoPath, $report_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao atualizar caminho do arquivo: " . $stmt->error);
                }
            } else {
                throw new Exception("Erro ao fazer upload do arquivo");
            }
        }

        $success_message = "Dados salvos com sucesso!";
        header("Location: meus-scripts.php");
        exit;

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Incluir o header depois de todo o processamento
include_once '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Gerar Script - Sou + Digital</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../assets/img/Icon geral.png" rel="icon">
    <link href="../assets/img/Icon geral.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i" rel="stylesheet">

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
        .form-floating > .form-control[type="file"] {
            height: calc(3.5rem + 2px);
            line-height: 1.25;
        }
    </style>
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
                            <?php echo htmlspecialchars($user['name']); ?>
                        </span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>Nome: <?php echo htmlspecialchars($user['name']); ?></h6>
                            <span>Usuário: <?php echo htmlspecialchars($user['username']); ?></span>
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
                                <span>Deslogar</span>
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
                                <h1>Relatório de Atendimento</h1>
                                <nav>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                                        <li class="breadcrumb-item active">Gerar Script</li>
                                    </ol>
                                </nav>
                            </div>

                            <form id="scriptForm" enctype="multipart/form-data" method="POST">
                                <!-- Data do Chamado -->
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="dataChamado" name="dataChamado">
                                    <label for="dataChamado">Data do chamado:</label>
                                </div>

                                <!-- Número do Chamado -->
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="numeroChamado" name="numeroChamado">
                                    <label for="numeroChamado">Número do chamado:</label>
                                </div>

                                <!-- Cliente -->
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="cliente" name="cliente">
                                    <label for="cliente">Cliente:</label>
                                </div>

                                <!-- Nome de quem informou o chamado -->
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="nomeInformante" name="nomeInformante">
                                    <label for="nomeInformante">Nome de quem informou o chamado:</label>
                                </div>

                                <!-- Quantidade de Patrimônios Tratados -->
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="quantidadePatrimonios" name="quantidadePatrimonios">
                                    <label for="quantidadePatrimonios">Quantidade de patrimônios tratados:</label>
                                </div>

                                <!-- KM Inicial e Final -->
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="kmInicial" name="kmInicial">
                                    <label for="kmInicial">KM inicial:</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="kmFinal" name="kmFinal">
                                    <label for="kmFinal">KM final:</label>
                                </div>

                                <!-- Horários de Chegada e Saída -->
                                <div class="form-floating mb-3">
                                    <input type="time" class="form-control" id="horaChegada" name="horaChegada">
                                    <label for="horaChegada">Horário de chegada no chamado:</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="time" class="form-control" id="horaSaida" name="horaSaida">
                                    <label for="horaSaida">Horário de saída do chamado:</label>
                                </div>

                                <!-- Endereços -->
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="enderecoPartida" name="enderecoPartida">
                                    <label for="enderecoPartida">Endereço de partida:</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="enderecoChegada" name="enderecoChegada">
                                    <label for="enderecoChegada">Endereço de chegada:</label>
                                </div>

                                <!-- Informações Adicionais -->
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="informacoesAdicionais" name="informacoesAdicionais" style="height: 250px"></textarea>
                                    <label for="informacoesAdicionais">Breve descrição do chamado:</label>
                                </div>

                                <!-- Upload de Arquivo -->
                                <div class="form-floating mb-3">
                                    <input type="file" class="form-control" id="arquivo" name="arquivo" accept=".pdf">
                                    <label for="arquivo">Anexar RAT (PDF):</label>
                                </div>

                                <!-- Ações -->
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-outline-primary">Salvar e Enviar</button>
                                    <button type="button" class="btn btn-outline-danger" onclick="limparFormulario()">Apagar Tudo</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

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

    <!-- Modal de Sucesso -->
    <div class="modal fade" id="sucessoModal" tabindex="-1" aria-labelledby="sucessoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sucessoModalLabel">Sucesso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="mensagemSucesso"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Erro -->
    <div class="modal fade" id="erroModal" tabindex="-1" aria-labelledby="erroModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="erroModalLabel">Erro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="mensagemErro"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Função para limpar o formulário
    function limparFormulario() {
        document.getElementById('scriptForm').reset();
        mostrarSucesso('Formulário limpo com sucesso!');
    }

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

    <?php if ($success_message): ?>
    mostrarSucesso("<?php echo htmlspecialchars($success_message); ?>");
    <?php endif; ?>

    <?php if ($error_message): ?>
    mostrarErro("<?php echo htmlspecialchars($error_message); ?>");
    <?php endif; ?>
    </script>
</body>
</html> 