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
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .status-admin {
            background-color: #198754;
            color: white;
        }
        .status-user {
            background-color: #6c757d;
            color: white;
        }
        .table-responsive {
            margin: 15px 0;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .search-filters {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .btn-icon {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s;
        }
        .modal-header {
            background-color: #f8f9fa;
        }
        .password-group {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .highlight {
            background-color: #fff3cd;
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
                            <?php echo htmlspecialchars($user['name'] ?? 'Usuário', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
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

    <?php include_once('../includes/sidebar.php'); ?>

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

                            <div class="search-filters">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" id="searchTable" placeholder="Pesquisar usuários...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="filterAdmin">
                                            <option value="">Todos os tipos</option>
                                            <option value="1">Acesso Administrador</option>
                                            <option value="0">Acesso Usuário</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-outline-secondary" id="clearFilters">
                                            <i class="bi bi-x-circle"></i> Limpar filtros
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th scope="col" width="5%">#</th>
                                            <th scope="col" width="25%">Nome</th>
                                            <th scope="col" width="20%">Usuário</th>
                                            <th scope="col" width="25%">Email</th>
                                            <th scope="col" width="10%">Tipo</th>
                                            <th scope="col" width="15%">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <th scope="row"><?php echo htmlspecialchars($row['id']); ?></th>
                                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $row['is_admin'] ? 'status-admin' : 'status-user'; ?>">
                                                        <?php echo $row['is_admin'] ? 'Acesso Administrador' : 'Acesso Usuário'; ?>
                                                    </span>
                                                </td>
                                                <td class="action-buttons">
                                                    <a href="editar-usuario.php?id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-primary btn-sm" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Editar usuário">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            onclick="confirmarExclusao(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>')"
                                                            data-bs-toggle="tooltip" 
                                                            title="Excluir usuário">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
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

    <script>
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Inicializar toasts
        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
        var toastList = toastElList.map(function(toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 })
        });
        toastList.forEach(toast => toast.show());

        // Função para confirmar exclusão
        function confirmarExclusao(id, nome) {
            Swal.fire({
                title: 'Confirmar exclusão?',
                html: `Você está prestes a excluir o usuário <strong>${nome}</strong>.<br>Esta ação não pode ser desfeita.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'excluir-usuario.php?id=' + id;
                }
            });
        }

        // Preview de imagem
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = preview.querySelector('img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        // Toggle de senha
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Pesquisa na tabela
        document.getElementById('searchTable').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const table = document.querySelector('.datatable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell.textContent.toLowerCase().indexOf(searchText) > -1) {
                        found = true;
                        break;
                    }
                }

                row.style.display = found ? '' : 'none';
            }
        });

        // Filtro de tipo de usuário
        document.getElementById('filterAdmin').addEventListener('change', function() {
            const filterValue = this.value;
            const table = document.querySelector('.datatable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const adminCell = row.querySelector('.status-badge');
                
                if (filterValue === '') {
                    row.style.display = '';
                } else {
                    const isAdmin = adminCell.classList.contains('status-admin');
                    row.style.display = (filterValue === '1' && isAdmin) || (filterValue === '0' && !isAdmin) ? '' : 'none';
                }
            }
        });

        // Limpar filtros
        document.getElementById('clearFilters').addEventListener('click', function() {
            document.getElementById('searchTable').value = '';
            document.getElementById('filterAdmin').value = '';
            const table = document.querySelector('.datatable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                rows[i].style.display = '';
            }
        });

        // Validação de força da senha
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            let strength = 0;
            let html = '';

            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[$@#&!]+/)) strength += 1;

            switch (strength) {
                case 0:
                    html = '<div class="progress" style="height: 5px;"><div class="progress-bar bg-danger" style="width: 25%"></div></div><small class="text-danger">Senha muito fraca</small>';
                    break;
                case 1:
                    html = '<div class="progress" style="height: 5px;"><div class="progress-bar bg-warning" style="width: 50%"></div></div><small class="text-warning">Senha fraca</small>';
                    break;
                case 2:
                    html = '<div class="progress" style="height: 5px;"><div class="progress-bar bg-info" style="width: 75%"></div></div><small class="text-info">Senha média</small>';
                    break;
                case 3:
                    html = '<div class="progress" style="height: 5px;"><div class="progress-bar bg-success" style="width: 100%"></div></div><small class="text-success">Senha forte</small>';
                    break;
                case 4:
                    html = '<div class="progress" style="height: 5px;"><div class="progress-bar bg-success" style="width: 100%"></div></div><small class="text-success">Senha muito forte</small>';
                    break;
            }

            strengthDiv.innerHTML = html;
        });
    </script>

    <!-- Adicionar SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html> 