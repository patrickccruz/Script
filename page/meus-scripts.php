<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: autenticacao.php");
    exit;
}

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
} else {
    $user = ['id' => 0, 'name' => 'Usuário', 'username' => 'username'];
}

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'sou_digital');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Buscar os scripts do usuário
$stmt = $conn->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY data_chamado DESC");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Meus Scripts - Sou + Digital</title>
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
    <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="../assets/css/style.css" rel="stylesheet">

    <style>
        .script-card {
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .script-card:hover {
            transform: translateY(-5px);
        }
        .filter-section {
            background: #f6f9ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        .search-box i {
            position: absolute;
            left: 15px;
            top: 12px;
            color: #666;
        }
        .search-box input {
            padding-left: 40px;
        }
        .card-footer {
            background: transparent;
            border-top: 1px solid rgba(0,0,0,.125);
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
        .empty-state i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <!-- Header -->
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
                                <h1>Meus Scripts</h1>
                                <nav>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                                        <li class="breadcrumb-item active">Meus Scripts</li>
                                    </ol>
                                </nav>
                            </div>

                            <!-- Filtros e Pesquisa -->
                            <div class="filter-section">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="search-box">
                                            <i class="bi bi-search"></i>
                                            <input type="text" class="form-control" id="searchInput" placeholder="Pesquisar scripts...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="filterMonth">
                                            <option value="">Todos os Meses</option>
                                            <option value="1">Janeiro</option>
                                            <option value="2">Fevereiro</option>
                                            <option value="3">Março</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Maio</option>
                                            <option value="6">Junho</option>
                                            <option value="7">Julho</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Setembro</option>
                                            <option value="10">Outubro</option>
                                            <option value="11">Novembro</option>
                                            <option value="12">Dezembro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="filterYear">
                                            <?php
                                            $currentYear = date('Y');
                                            for ($year = $currentYear; $year >= $currentYear - 2; $year--) {
                                                echo "<option value='$year'>$year</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Cards dos Scripts -->
                            <div class="row" id="scriptsContainer">
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <div class="col-md-6 col-lg-4 mb-4 script-card-container">
                                            <div class="card script-card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        Chamado #<?php echo htmlspecialchars($row['numero_chamado']); ?>
                                                    </h5>
                                                    <div class="status-badge">
                                                        <?php if ($row['arquivo_path']): ?>
                                                            <span class="badge bg-success">RAT Anexado</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Sem RAT</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="card-text">
                                                        <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($row['data_chamado'])); ?><br>
                                                        <strong>Cliente:</strong> <?php echo htmlspecialchars($row['cliente']); ?><br>
                                                        <strong>Informante:</strong> <?php echo htmlspecialchars($row['nome_informante']); ?>
                                                    </p>
                                                </div>
                                                <div class="card-footer">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detalhesModal<?php echo $row['id']; ?>">
                                                            <i class="bi bi-eye"></i> Ver Detalhes
                                                        </button>
                                                        <?php if ($row['arquivo_path']): ?>
                                                            <a href="<?php echo $row['arquivo_path']; ?>" class="btn btn-info btn-sm" target="_blank">
                                                                <i class="bi bi-file-pdf"></i> Ver RAT
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal de Detalhes -->
                                        <div class="modal fade" id="detalhesModal<?php echo $row['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-file-text me-2"></i>
                                                            Detalhes do Script #<?php echo htmlspecialchars($row['numero_chamado']); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="list-group list-group-flush">
                                                            <!-- Informações do Chamado -->
                                                            <div class="list-group-item">
                                                                <h6 class="mb-3 text-primary">
                                                                    <i class="bi bi-info-circle me-2"></i>Informações do Chamado
                                                                </h6>
                                                                <div class="ms-4">
                                                                    <div class="row mb-2">
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><i class="bi bi-calendar me-2"></i><strong>Data:</strong></p>
                                                                            <p class="text-muted ms-4"><?php echo date('d/m/Y', strtotime($row['data_chamado'])); ?></p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><i class="bi bi-hash me-2"></i><strong>Número:</strong></p>
                                                                            <p class="text-muted ms-4"><?php echo htmlspecialchars($row['numero_chamado']); ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Informações do Cliente -->
                                                            <div class="list-group-item">
                                                                <h6 class="mb-3 text-primary">
                                                                    <i class="bi bi-person me-2"></i>Informações do Cliente
                                                                </h6>
                                                                <div class="ms-4">
                                                                    <div class="row mb-2">
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><i class="bi bi-building me-2"></i><strong>Cliente:</strong></p>
                                                                            <p class="text-muted ms-4"><?php echo htmlspecialchars($row['cliente']); ?></p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><i class="bi bi-person-badge me-2"></i><strong>Informante:</strong></p>
                                                                            <p class="text-muted ms-4"><?php echo htmlspecialchars($row['nome_informante']); ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Informações de Deslocamento -->
                                                            <div class="list-group-item">
                                                                <h6 class="mb-3 text-primary">
                                                                    <i class="bi bi-geo-alt me-2"></i>Informações de Deslocamento
                                                                </h6>
                                                                <div class="ms-4">
                                                                    <div class="row mb-2">
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><i class="bi bi-speedometer2 me-2"></i><strong>KM Inicial:</strong></p>
                                                                            <p class="text-muted ms-4"><?php echo htmlspecialchars($row['km_inicial']); ?> km</p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><i class="bi bi-speedometer me-2"></i><strong>KM Final:</strong></p>
                                                                            <p class="text-muted ms-4"><?php echo htmlspecialchars($row['km_final']); ?> km</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row mb-2">
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><i class="bi bi-clock me-2"></i><strong>Hora Chegada:</strong></p>
                                                                            <p class="text-muted ms-4"><?php echo htmlspecialchars($row['hora_chegada']); ?></p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><i class="bi bi-clock-history me-2"></i><strong>Hora Saída:</strong></p>
                                                                            <p class="text-muted ms-4"><?php echo htmlspecialchars($row['hora_saida']); ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row mb-2">
                                                                        <div class="col-12">
                                                                            <p class="mb-1"><i class="bi bi-geo me-2"></i><strong>Endereço de Partida:</strong></p>
                                                                            <p class="text-muted ms-4"><?php echo htmlspecialchars($row['endereco_partida']); ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-12">
                                                                            <p class="mb-1"><i class="bi bi-geo-fill me-2"></i><strong>Endereço de Chegada:</strong></p>
                                                                            <p class="text-muted ms-4"><?php echo htmlspecialchars($row['endereco_chegada']); ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Informações do Serviço -->
                                                            <div class="list-group-item">
                                                                <h6 class="mb-3 text-primary">
                                                                    <i class="bi bi-tools me-2"></i>Informações do Serviço
                                                                </h6>
                                                                <div class="ms-4">
                                                                    <div class="row mb-2">
                                                                        <div class="col-12">
                                                                            <p class="mb-1">
                                                                                <i class="bi bi-boxes me-2"></i>
                                                                                <strong>Quantidade de Patrimônios:</strong>
                                                                            </p>
                                                                            <p class="text-muted ms-4"><?php echo htmlspecialchars($row['quantidade_patrimonios']); ?> unidades</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-12">
                                                                            <p class="mb-1">
                                                                                <i class="bi bi-card-text me-2"></i>
                                                                                <strong>Informações Adicionais:</strong>
                                                                            </p>
                                                                            <div class="p-3 bg-light rounded ms-4">
                                                                                <?php echo nl2br(htmlspecialchars($row['informacoes_adicionais'])); ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <?php if ($row['arquivo_path']): ?>
                                                            <a href="<?php echo $row['arquivo_path']; ?>" class="btn btn-info" target="_blank">
                                                                <i class="bi bi-file-pdf"></i> Abrir RAT
                                                            </a>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <i class="bi bi-x-circle me-1"></i>Fechar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="empty-state">
                                            <i class="bi bi-journal-x"></i>
                                            <h4>Nenhum script encontrado</h4>
                                            <p>Você ainda não criou nenhum script. Clique no botão abaixo para criar seu primeiro script.</p>
                                            <a href="gerar-script.php" class="btn btn-primary">
                                                <i class="bi bi-plus-circle"></i> Criar Novo Script
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
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
    <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../assets/js/main.js"></script>

    <!-- Filtro e Pesquisa Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterMonth = document.getElementById('filterMonth');
            const filterYear = document.getElementById('filterYear');
            const cards = document.querySelectorAll('.script-card-container');

            function filterCards() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedMonth = filterMonth.value;
                const selectedYear = filterYear.value;

                cards.forEach(card => {
                    const cardText = card.textContent.toLowerCase();
                    const cardDate = card.querySelector('.card-text strong:first-child').nextSibling.textContent.trim();
                    const [cardDay, cardMonth, cardYear] = cardDate.split('/');

                    const matchesSearch = cardText.includes(searchTerm);
                    const matchesMonth = selectedMonth === '' || parseInt(cardMonth) === parseInt(selectedMonth);
                    const matchesYear = selectedYear === '' || cardYear === selectedYear;

                    if (matchesSearch && matchesMonth && matchesYear) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Mostrar mensagem quando não houver resultados
                const visibleCards = document.querySelectorAll('.script-card-container[style=""]');
                const emptyState = document.querySelector('.empty-state');
                if (visibleCards.length === 0) {
                    if (!emptyState) {
                        const container = document.getElementById('scriptsContainer');
                        container.innerHTML += `
                            <div class="col-12 empty-state">
                                <i class="bi bi-search"></i>
                                <h4>Nenhum resultado encontrado</h4>
                                <p>Tente ajustar seus filtros de pesquisa.</p>
                            </div>
                        `;
                    }
                } else {
                    const emptyState = document.querySelector('.empty-state');
                    if (emptyState) {
                        emptyState.remove();
                    }
                }
            }

            searchInput.addEventListener('input', filterCards);
            filterMonth.addEventListener('change', filterCards);
            filterYear.addEventListener('change', filterCards);
        });
    </script>
</body>
</html> 