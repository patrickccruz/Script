<?php
session_start();

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

$is_page = true; // Indica que estamos em uma página dentro do diretório 'page'
include_once '../includes/header.php';

// Gerar token CSRF
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Conexão com o banco de dados
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

    // Buscar todos os usuários
    $result = $conn->query("SELECT id, name, username, email, is_admin FROM users ORDER BY name");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Gerenciar Usuários - Sou + Digital</title>
    
    <!-- Favicons -->
    <link href="../assets/img/Icon geral.png" rel="icon">
    <link href="../assets/img/Icon geral.png" rel="apple-touch-icon">
    
    <!-- Google Fonts -->
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
        .user-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .user-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #4154f1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .user-card:hover::before {
            opacity: 1;
        }

        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .status-admin {
            background: linear-gradient(135deg, #198754, #157347);
            color: white;
        }

        .status-user {
            background: linear-gradient(135deg, #6c757d, #565e64);
            color: white;
        }

        .filter-section {
            background: linear-gradient(to right, #f6f9ff, #ffffff);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .search-box {
            position: relative;
            margin-bottom: 20px;
        }

        .search-box input {
            padding-left: 40px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: #4154f1;
            box-shadow: 0 0 0 0.2rem rgba(65, 84, 241, 0.25);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .form-select {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: #4154f1;
            box-shadow: 0 0 0 0.2rem rgba(65, 84, 241, 0.25);
        }

        .card-info {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: #6c757d;
        }

        .card-info i {
            margin-right: 8px;
            font-size: 0.9rem;
        }

        .card-footer {
            background: transparent;
            border-top: 1px solid rgba(0,0,0,0.05);
            padding: 1rem;
        }

        .btn {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn i {
            font-size: 0.9rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            margin: 2rem 0;
        }

        .empty-state i {
            font-size: 3rem;
            color: #4154f1;
            margin-bottom: 1rem;
        }

        .empty-state h4 {
            color: #012970;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 1.5rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .user-card-container {
            animation: fadeIn 0.5s ease forwards;
        }

        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .modal-header {
            border-radius: 12px 12px 0 0;
            background: linear-gradient(135deg, #4154f1, #2536b8);
            padding: 1.5rem;
            color: white;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
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
</head>

<body>


    <!-- <?php include_once('../includes/sidebar.php'); ?> -->

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Gerenciar Usuários</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active">Gerenciar Usuários</li>
                </ol>
            </nav>
        </div>

        <?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): ?>
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-x-circle me-2"></i>
                                <?php 
                                    echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8');
                                    unset($_SESSION['error']);
                                ?>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php 
                                    echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8');
                                    unset($_SESSION['success']);
                                ?>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">Lista de Usuários</h5>
                                <button type="button" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#novoUsuarioModal">
                                    <i class="bi bi-plus-circle"></i> Novo Usuário
                                </button>
                            </div>

                            <div class="loading">
                                <div class="loading-spinner"></div>
                            </div>

                            <div class="filter-section">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="search-box">
                                            <i class="bi bi-search"></i>
                                            <input type="text" class="form-control" id="searchInput" placeholder="Pesquisar usuários...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="filterAdmin">
                                            <option value="">Todos os tipos</option>
                                            <option value="1">Acesso Administrador</option>
                                            <option value="0">Acesso Usuário</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="entriesPerPage">
                                            <option value="10">10 registros por página</option>
                                            <option value="25">25 registros por página</option>
                                            <option value="50">50 registros por página</option>
                                            <option value="100">100 registros por página</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="usersContainer">
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <div class="col-md-6 col-lg-4 user-card-container">
                                            <div class="card user-card h-100">
                                                <div class="card-body">
                                                    <span class="status-badge <?php echo $row['is_admin'] ? 'status-admin' : 'status-user'; ?>">
                                                        <?php echo $row['is_admin'] ? 'Acesso Administrador' : 'Acesso Usuário'; ?>
                                                    </span>

                                                    <h5 class="card-title">
                                                        <?php echo htmlspecialchars($row['name']); ?>
                                                    </h5>

                                                    <div class="card-info">
                                                        <i class="bi bi-person"></i>
                                                        <?php echo htmlspecialchars($row['username']); ?>
                                                    </div>

                                                    <div class="card-info">
                                                        <i class="bi bi-envelope"></i>
                                                        <?php echo htmlspecialchars($row['email']); ?>
                                                    </div>

                                                    <div class="card-info">
                                                        <i class="bi bi-key"></i>
                                                        ID: <?php echo htmlspecialchars($row['id']); ?>
                                                    </div>
                                                </div>

                                                <div class="card-footer">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <a href="editar-usuario.php?id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="bi bi-pencil"></i> Editar
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-danger btn-sm"
                                                                onclick="confirmarExclusao(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>')">
                                                            <i class="bi bi-trash"></i> Excluir
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="empty-state">
                                            <i class="bi bi-people"></i>
                                            <h4>Nenhum usuário encontrado</h4>
                                            <p>Não há usuários cadastrados no sistema.</p>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novoUsuarioModal">
                                                <i class="bi bi-person-plus"></i> Adicionar Novo Usuário
                                            </button>
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

    <!-- Modal Novo Usuário -->
    <div class="modal fade" id="novoUsuarioModal" tabindex="-1" aria-labelledby="novoUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="novoUsuarioModalLabel">
                        <i class="bi bi-person-plus"></i> Adicionar novo usuário
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="processar-novo-usuario.php" method="POST" class="needs-validation" novalidate enctype="multipart/form-data" id="newUserForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]{2,}" 
                                   title="Nome deve conter apenas letras e espaços">
                            <div class="invalid-feedback">
                                Por favor, insira um nome válido.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">
                                Por favor, insira um email válido.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Nome de Usuário</label>
                            <input type="text" class="form-control" id="username" name="username" required
                                   pattern="[a-zA-Z0-9_]{3,}" 
                                   title="Nome de usuário deve conter apenas letras, números e underscore">
                            <div class="invalid-feedback">
                                Por favor, insira um nome de usuário válido.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <div class="password-group">
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                            </div>
                            <div class="invalid-feedback">
                                A senha deve ter no mínimo 8 caracteres.
                            </div>
                            <div class="password-strength mt-2" id="passwordStrength"></div>
                        </div>

                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Imagem de Perfil</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" 
                                   accept="image/*" onchange="previewImage(this)">
                            <div class="form-text">Opcional. Apenas imagens (JPG, PNG, GIF).</div>
                            <div id="imagePreview" class="mt-2 text-center" style="display: none;">
                                <img src="" alt="Preview" style="max-width: 200px; max-height: 200px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin">
                                <label class="form-check-label" for="is_admin">
                                    Usuário Administrador
                                </label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Criar Usuário
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span>Sou + Digital</span></strong>. Todos os direitos reservados
        </div>
    </footer>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

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

    <!-- Script para garantir que o toggle da sidebar funcione -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
            if (toggleSidebarBtn) {
                toggleSidebarBtn.addEventListener('click', function() {
                    document.querySelector('body').classList.toggle('toggle-sidebar');
                });
            }
        });
    </script>

    <!-- Adicionar SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let searchInput, filterAdmin, entriesPerPage, cards;

        // Função global para limpar filtros
        function clearFilters() {
            searchInput.value = '';
            filterAdmin.value = '';
            entriesPerPage.value = '10';
            filterCards();
        }

        // Função global para filtrar cards
        function filterCards() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedType = filterAdmin.value;
            const perPage = parseInt(entriesPerPage.value);
            let visibleCount = 0;

            cards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                const isAdmin = card.querySelector('.status-admin') !== null;
                const matchesSearch = searchTerm === '' || cardText.includes(searchTerm);
                const matchesType = selectedType === '' || 
                                  (selectedType === '1' && isAdmin) || 
                                  (selectedType === '0' && !isAdmin);
                const withinPagination = visibleCount < perPage;

                if (matchesSearch && matchesType && withinPagination) {
                    card.style.display = '';
                    card.classList.add('animate__animated', 'animate__fadeIn');
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            updateEmptyState();
        }

        // Função global para atualizar estado vazio
        function updateEmptyState() {
            const visibleCards = Array.from(cards).filter(card => card.style.display !== 'none');
            const container = document.getElementById('usersContainer');
            const existingEmptyState = container.querySelector('.empty-state');

            if (visibleCards.length === 0) {
                if (!existingEmptyState) {
                    const emptyStateDiv = document.createElement('div');
                    emptyStateDiv.className = 'col-12';
                    emptyStateDiv.innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-search"></i>
                            <h4>Nenhum resultado encontrado</h4>
                            <p>Tente ajustar seus filtros de pesquisa.</p>
                            <button type="button" class="btn btn-secondary" onclick="clearFilters()">
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

        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar variáveis globais
            searchInput = document.getElementById('searchInput');
            filterAdmin = document.getElementById('filterAdmin');
            entriesPerPage = document.getElementById('entriesPerPage');
            cards = document.querySelectorAll('.user-card-container');

            // Adicionar event listeners
            searchInput.addEventListener('input', filterCards);
            filterAdmin.addEventListener('change', filterCards);
            entriesPerPage.addEventListener('change', filterCards);

            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Adicionar link para Animate.css
            if (!document.querySelector('link[href*="animate.css"]')) {
                const animateCssLink = document.createElement('link');
                animateCssLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css';
                animateCssLink.rel = 'stylesheet';
                document.head.appendChild(animateCssLink);
            }
        });

        // Função para confirmar exclusão com animação melhorada
        function confirmarExclusao(id, nome) {
            Swal.fire({
                title: 'Confirmar exclusão?',
                html: `Você está prestes a excluir o usuário <strong>${nome}</strong>.<br>Esta ação não pode ser desfeita.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('.loading').style.display = 'flex';
                    window.location.href = 'excluir-usuario.php?id=' + id;
                }
            });
        }

        // Preview de imagem com feedback visual
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = preview.querySelector('img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                    preview.classList.add('animate__animated', 'animate__fadeIn');
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.classList.add('animate__animated', 'animate__fadeOut');
                setTimeout(() => {
                    preview.style.display = 'none';
                    preview.classList.remove('animate__animated', 'animate__fadeOut');
                }, 500);
            }
        }

        // Toggle de senha com feedback visual
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
            
            // Feedback visual
            this.classList.add('animate__animated', 'animate__flipInY');
            setTimeout(() => {
                this.classList.remove('animate__animated', 'animate__flipInY');
            }, 500);
        });

        // Validação de força da senha com feedback visual melhorado
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            let strength = 0;
            let html = '';

            // Critérios de força
            const criteria = {
                length: password.length >= 8,
                lowercase: /[a-z]/.test(password),
                uppercase: /[A-Z]/.test(password),
                numbers: /[0-9]/.test(password),
                special: /[$@#&!]/.test(password)
            };

            // Calcular força
            strength = Object.values(criteria).filter(Boolean).length;

            // Gerar HTML com animação
            const getStrengthClass = (s) => {
                if (s <= 1) return 'danger';
                if (s <= 2) return 'warning';
                if (s <= 3) return 'info';
                return 'success';
            };

            const strengthText = {
                0: 'Muito fraca',
                1: 'Fraca',
                2: 'Média',
                3: 'Forte',
                4: 'Muito forte',
                5: 'Excelente'
            };

            html = `
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-${getStrengthClass(strength)}" 
                         style="width: ${(strength/5)*100}%"
                         role="progressbar"
                         aria-valuenow="${(strength/5)*100}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>
                </div>
                <small class="text-${getStrengthClass(strength)} mt-1 d-block">
                    ${strengthText[strength]}
                </small>
                <div class="criteria-list mt-2">
                    <small class="d-block ${criteria.length ? 'text-success' : 'text-muted'}">
                        <i class="bi ${criteria.length ? 'bi-check-circle-fill' : 'bi-x-circle'}"></i>
                        Mínimo 8 caracteres
                    </small>
                    <small class="d-block ${criteria.lowercase ? 'text-success' : 'text-muted'}">
                        <i class="bi ${criteria.lowercase ? 'bi-check-circle-fill' : 'bi-x-circle'}"></i>
                        Letra minúscula
                    </small>
                    <small class="d-block ${criteria.uppercase ? 'text-success' : 'text-muted'}">
                        <i class="bi ${criteria.uppercase ? 'bi-check-circle-fill' : 'bi-x-circle'}"></i>
                        Letra maiúscula
                    </small>
                    <small class="d-block ${criteria.numbers ? 'text-success' : 'text-muted'}">
                        <i class="bi ${criteria.numbers ? 'bi-check-circle-fill' : 'bi-x-circle'}"></i>
                        Número
                    </small>
                    <small class="d-block ${criteria.special ? 'text-success' : 'text-muted'}">
                        <i class="bi ${criteria.special ? 'bi-check-circle-fill' : 'bi-x-circle'}"></i>
                        Caractere especial
                    </small>
                </div>
            `;

            strengthDiv.innerHTML = html;
        });
    </script>
</body>
</html> 