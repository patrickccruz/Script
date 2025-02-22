<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true || !isset($_SESSION['user']['is_admin']) || $_SESSION['user']['is_admin'] !== true) {
    header("Location: autenticacao.php");
    exit;
}

$is_page = true; // Indica que estamos em uma página dentro do diretório 'page'
include_once '../includes/header.php';

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

<style>
    .filter-section {
        background: #ffffff;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }

    .filter-section:hover {
        box-shadow: 0 0 25px rgba(0,0,0,0.1);
    }

    .search-box {
        position: relative;
        margin-bottom: 20px;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        transition: color 0.3s ease;
    }

    .search-box input {
        padding-left: 45px;
        height: 45px;
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .search-box input:focus {
        border-color: #4154f1;
        box-shadow: 0 0 0 0.2rem rgba(65, 84, 241, 0.25);
    }

    .search-box input:focus + i {
        color: #4154f1;
    }

    .form-select {
        height: 45px;
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-select:focus {
        border-color: #4154f1;
        box-shadow: 0 0 0 0.2rem rgba(65, 84, 241, 0.25);
    }

    .reembolso-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .reembolso-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .card-body {
        padding: 1.5rem;
    }

    .user-info {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 15px;
        border: 3px solid #fff;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .card-text {
        color: #6c757d;
        margin-bottom: 15px;
        line-height: 1.6;
    }

    .card-text strong {
        color: #012970;
    }

    .card-footer {
        background: transparent;
        border-top: 1px solid rgba(0,0,0,0.05);
        padding: 1rem 1.5rem;
    }

    .btn-ver-detalhes {
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #f8f9fa;
        border-radius: 15px;
        margin: 20px 0;
    }

    .empty-state i {
        font-size: 64px;
        color: #adb5bd;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: #012970;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #6c757d;
    }

    .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }

    .modal-header {
        padding: 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .list-group-item {
        padding: 1.5rem;
        border: none;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .file-preview {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .file-item {
        background: #f8f9fa;
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 0.9em;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        text-decoration: none;
        color: #012970;
    }

    .file-item:hover {
        background: #e9ecef;
        transform: translateY(-2px);
    }

    /* Animações */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .reembolso-card-container {
        animation: fadeIn 0.5s ease forwards;
    }

    /* Loading State */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        visibility: hidden;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .loading-overlay.active {
        visibility: visible;
        opacity: 1;
    }

    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #4154f1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<?php include_once '../includes/sidebar.php'; ?>

<div class="loading-overlay">
    <div class="loading-spinner"></div>
</div>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="pagetitle">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h1>Todos os Reembolsos</h1>
                                    <nav>
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                                            <li class="breadcrumb-item">Administração</li>
                                            <li class="breadcrumb-item active">Todos os Reembolsos</li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary" id="refreshBtn">
                                        <i class="bi bi-arrow-clockwise"></i> Atualizar
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown">
                                            <i class="bi bi-download"></i> Exportar
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" id="exportExcel"><i class="bi bi-file-earmark-excel"></i> Excel</a></li>
                                            <li><a class="dropdown-item" href="#" id="exportPDF"><i class="bi bi-file-earmark-pdf"></i> PDF</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros e Pesquisa -->
                        <div class="filter-section">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="search-box">
                                        <i class="bi bi-search"></i>
                                        <input type="text" class="form-control" id="searchInput" placeholder="Pesquisar reembolsos por nome, número do chamado ou descrição...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="filterMonth">
                                        <option value="">Todos os Meses</option>
                                        <?php
                                        $meses = [
                                            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',
                                            4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
                                            7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro',
                                            10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                                        ];
                                        foreach ($meses as $num => $nome) {
                                            echo "<option value='$num'>$nome</option>";
                                        }
                                        ?>
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

<?php include_once '../includes/footer.php'; ?>

<!-- Scripts específicos da página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterMonth = document.getElementById('filterMonth');
    const filterYear = document.getElementById('filterYear');
    const cards = document.querySelectorAll('.reembolso-card-container');
    const loadingOverlay = document.querySelector('.loading-overlay');
    const refreshBtn = document.getElementById('refreshBtn');

    // Função para mostrar loading
    function showLoading() {
        loadingOverlay.classList.add('active');
    }

    // Função para esconder loading
    function hideLoading() {
        loadingOverlay.classList.remove('active');
    }

    // Função para atualizar a página
    refreshBtn.addEventListener('click', function() {
        showLoading();
        location.reload();
    });

    function getDateFromText(text) {
        const match = text.match(/(\d{2})\/(\d{2})\/(\d{4})/);
        if (match) {
            return {
                day: match[1],
                month: match[2],
                year: match[3]
            };
        }
        return null;
    }

    function filterCards() {
        showLoading();
        
        const searchTerm = searchInput.value.toLowerCase();
        const selectedMonth = filterMonth.value;
        const selectedYear = filterYear.value;

        cards.forEach(card => {
            const cardText = card.textContent.toLowerCase();
            
            // Extrai a data do cartão
            const dateElement = Array.from(card.querySelectorAll('.card-text strong')).find(el => el.textContent.includes('Data do Chamado:'));
            let dateInfo = null;
            if (dateElement) {
                const dateText = dateElement.nextSibling.textContent.trim();
                dateInfo = getDateFromText(dateText);
            }

            // Aplica os filtros
            const matchesSearch = searchTerm === '' || cardText.includes(searchTerm);
            const matchesMonth = !selectedMonth || (dateInfo && parseInt(dateInfo.month) === parseInt(selectedMonth));
            const matchesYear = !selectedYear || (dateInfo && dateInfo.year === selectedYear);

            if (matchesSearch && matchesMonth && matchesYear) {
                card.style.display = '';
                card.style.animation = 'fadeIn 0.5s ease forwards';
            } else {
                card.style.display = 'none';
            }
        });

        setTimeout(updateEmptyState, 100);
        setTimeout(hideLoading, 300);
    }

    function updateEmptyState() {
        const visibleCards = Array.from(cards).filter(card => card.style.display !== 'none');
        const container = document.getElementById('reembolsosContainer');
        const existingEmptyState = container.querySelector('.empty-state');

        if (visibleCards.length === 0) {
            if (!existingEmptyState) {
                const emptyStateDiv = document.createElement('div');
                emptyStateDiv.className = 'col-12';
                emptyStateDiv.innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-search"></i>
                        <h4>Nenhum resultado encontrado</h4>
                        <p>Tente ajustar seus filtros de pesquisa ou limpar os filtros para ver todos os reembolsos.</p>
                        <button class="btn btn-primary mt-3" onclick="clearFilters()">
                            <i class="bi bi-arrow-counterclockwise"></i> Limpar Filtros
                        </button>
                    </div>
                `;
                container.appendChild(emptyStateDiv);
            }
        } else if (existingEmptyState) {
            existingEmptyState.remove();
        }
    }

    // Função para limpar filtros
    window.clearFilters = function() {
        searchInput.value = '';
        filterMonth.value = '';
        filterYear.value = '';
        filterCards();
    }

    // Event listeners
    searchInput.addEventListener('input', filterCards);
    filterMonth.addEventListener('change', filterCards);
    filterYear.addEventListener('change', filterCards);

    // Inicialização
    filterCards();
    hideLoading();

    // Exportação
    document.getElementById('exportExcel').addEventListener('click', function(e) {
        e.preventDefault();
        alert('Funcionalidade de exportação para Excel será implementada em breve!');
    });

    document.getElementById('exportPDF').addEventListener('click', function(e) {
        e.preventDefault();
        alert('Funcionalidade de exportação para PDF será implementada em breve!');
    });
});
</script>
</body>
</html> 