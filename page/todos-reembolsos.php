<?php
session_start();
require_once '../db.php';

// Verificação de autenticação
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: autenticacao.php");
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

    // Buscar todos os reembolsos (visão de administrador)
    $result = $conn->query("SELECT reembolsos.*, users.name as user_name, users.profile_image 
                           FROM reembolsos 
                           JOIN users ON reembolsos.user_id = users.id 
                           ORDER BY reembolsos.created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Todos os Reembolsos - Sou + Digital</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <link href="../assets/img/Icon geral.png" rel="icon">
    <link href="../assets/img/Icon geral.png" rel="apple-touch-icon">
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i" rel="stylesheet">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">

    <style>
        .reembolso-card {
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .reembolso-card:hover {
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
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .file-preview {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .file-item {
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
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
                            <?php echo isset($user['name']) ? htmlspecialchars($user['name']) : 'Usuário'; ?>
                        </span>
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
                                <h1>Todos os Reembolsos</h1>
                                <nav>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                                        <li class="breadcrumb-item">Administração</li>
                                        <li class="breadcrumb-item active">Todos os Reembolsos</li>
                                    </ol>
                                </nav>
                            </div>

                            <!-- Filtros e Pesquisa -->
                            <div class="filter-section">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="search-box">
                                            <i class="bi bi-search"></i>
                                            <input type="text" class="form-control" id="searchInput" placeholder="Pesquisar reembolsos...">
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

                            <!-- Cards dos Reembolsos -->
                            <div class="row" id="reembolsosContainer">
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <div class="col-md-6 col-lg-4 mb-4 reembolso-card-container">
                                            <div class="card reembolso-card h-100">
                                                <div class="card-body">
                                                    <div class="user-info">
                                                        <?php if ($row['profile_image']): ?>
                                                            <img src="<?php echo htmlspecialchars('../uploads/' . $row['profile_image']); ?>" 
                                                                 alt="Profile" 
                                                                 class="user-avatar">
                                                        <?php else: ?>
                                                            <i class="bi bi-person-circle me-2" style="font-size: 2rem;"></i>
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($row['user_name']); ?></h6>
                                                            <small class="text-muted">
                                                                Chamado #<?php echo htmlspecialchars($row['numero_chamado']); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <p class="card-text">
                                                        <strong>Data do Chamado:</strong> <?php echo date('d/m/Y', strtotime($row['data_chamado'])); ?><br>
                                                        <strong>Criado em:</strong> <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                                    </p>
                                                    <p class="card-text text-truncate">
                                                        <strong>Descrição:</strong> <?php echo htmlspecialchars($row['informacoes_adicionais']); ?>
                                                    </p>
                                                </div>
                                                <div class="card-footer">
                                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detalhesModal<?php echo $row['id']; ?>">
                                                        <i class="bi bi-eye"></i> Ver Detalhes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal de Detalhes -->
                                        <div class="modal fade" id="detalhesModal<?php echo $row['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-receipt me-2"></i>
                                                            Detalhes do Reembolso
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="list-group list-group-flush">
                                                            <!-- Informações do Usuário -->
                                                            <div class="list-group-item">
                                                                <h6 class="mb-3 text-primary">
                                                                    <i class="bi bi-person me-2"></i>Informações do Solicitante
                                                                </h6>
                                                                <div class="ms-4 user-info">
                                                                    <?php if ($row['profile_image']): ?>
                                                                        <img src="<?php echo htmlspecialchars('../uploads/' . $row['profile_image']); ?>" 
                                                                             alt="Profile" 
                                                                             class="user-avatar">
                                                                    <?php else: ?>
                                                                        <i class="bi bi-person-circle me-2" style="font-size: 2rem;"></i>
                                                                    <?php endif; ?>
                                                                    <div>
                                                                        <h6 class="mb-0"><?php echo htmlspecialchars($row['user_name']); ?></h6>
                                                                        <small class="text-muted">Solicitante</small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Informações do Chamado -->
                                                            <div class="list-group-item">
                                                                <h6 class="mb-3 text-primary">
                                                                    <i class="bi bi-info-circle me-2"></i>Informações do Chamado
                                                                </h6>
                                                                <div class="ms-4">
                                                                    <div class="row mb-2">
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1">
                                                                                <i class="bi bi-calendar me-2"></i>
                                                                                <strong>Data do Chamado:</strong>
                                                                            </p>
                                                                            <p class="text-muted ms-4">
                                                                                <?php echo date('d/m/Y', strtotime($row['data_chamado'])); ?>
                                                                            </p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1">
                                                                                <i class="bi bi-hash me-2"></i>
                                                                                <strong>Número do Chamado:</strong>
                                                                            </p>
                                                                            <p class="text-muted ms-4">
                                                                                <?php echo htmlspecialchars($row['numero_chamado']); ?>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Descrição -->
                                                            <div class="list-group-item">
                                                                <h6 class="mb-3 text-primary">
                                                                    <i class="bi bi-card-text me-2"></i>Descrição
                                                                </h6>
                                                                <div class="ms-4">
                                                                    <div class="p-3 bg-light rounded">
                                                                        <?php echo nl2br(htmlspecialchars($row['informacoes_adicionais'])); ?>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Arquivos -->
                                                            <?php if ($row['arquivo_path']): ?>
                                                            <div class="list-group-item">
                                                                <h6 class="mb-3 text-primary">
                                                                    <i class="bi bi-files me-2"></i>Arquivos Anexados
                                                                </h6>
                                                                <div class="ms-4">
                                                                    <div class="file-preview">
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
                                                                        ?>
                                                                            <a href="<?php echo htmlspecialchars($arquivo); ?>" 
                                                                               target="_blank" 
                                                                               class="file-item text-decoration-none">
                                                                                <i class="bi <?php echo $icone; ?>"></i>
                                                                                <?php echo htmlspecialchars($nomeArquivo); ?>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
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
                                            <i class="bi bi-receipt-cutoff"></i>
                                            <h4>Nenhum reembolso encontrado</h4>
                                            <p>Não há reembolsos registrados no sistema.</p>
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

    <!-- Filtro e Pesquisa Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterMonth = document.getElementById('filterMonth');
            const filterYear = document.getElementById('filterYear');
            const cards = document.querySelectorAll('.reembolso-card-container');

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
                const visibleCards = document.querySelectorAll('.reembolso-card-container[style=""]');
                const emptyState = document.querySelector('.empty-state');
                if (visibleCards.length === 0) {
                    if (!emptyState) {
                        const container = document.getElementById('reembolsosContainer');
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