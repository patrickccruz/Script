<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: page/autenticacao.php");
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

// Buscar posts do blog
$sql = "SELECT p.*, u.name as author_name, 
        (SELECT COUNT(*) FROM blog_reacoes WHERE post_id = p.id) as total_reactions,
        (SELECT COUNT(*) FROM blog_comentarios WHERE post_id = p.id) as total_comments
        FROM blog_posts p 
        LEFT JOIN users u ON p.user_id = u.id 
        ORDER BY p.data_criacao DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Página Inicial - Sou + Digital</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/Icon geral.png" rel="icon">
    <link href="assets/img/Icon geral.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i" rel="stylesheet">

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
        .blog-post {
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }
        .blog-post:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .post-image-container {
            position: relative;
            overflow: hidden;
            height: 200px;
            cursor: pointer;
        }
        .post-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .post-image-container:hover .post-image {
            transform: scale(1.05);
        }
        .post-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .post-image-container:hover .post-overlay {
            opacity: 1;
        }
        .card-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        .card-title a {
            color: #012970;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .card-title a:hover {
            color: #0d6efd;
        }
        .card-text {
            color: #6c757d;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        .post-meta {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .post-stats {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .post-stats i {
            margin-right: 0.3rem;
        }
        .btn-novo-post {
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-novo-post:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
    </style>
</head>

<body>
    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="index.php" class="logo d-flex align-items-center">
                <img src="assets/img/Ico_geral.png" alt="Logo">
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
                            <a class="dropdown-item d-flex align-items-center" href="page/meu-perfil.php">
                                <i class="bi bi-person"></i>
                                <span>Meu Perfil</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="page/sair.php">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Deslogar</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <?php include_once 'includes/sidebar.php'; ?>

    <main id="main" class="main">
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title m-0">Blog Posts</h5>
                                <?php if (isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin'] === true): ?>
                                <a href="page/criar-post.php" class="btn btn-primary btn-novo-post">
                                    <i class="bi bi-plus-lg"></i> Novo Post
                                </a>
                                <?php endif; ?>
                            </div>

                            <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($post = $result->fetch_assoc()): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card blog-post">
                                                <?php if ($post['imagem_capa']): ?>
                                                <a href="page/visualizar-post.php?id=<?php echo $post['id']; ?>" class="post-image-container">
                                                    <img src="<?php echo htmlspecialchars($post['imagem_capa']); ?>" class="post-image" alt="<?php echo htmlspecialchars($post['titulo']); ?>">
                                                    <div class="post-overlay"></div>
                                                </a>
                                                <?php endif; ?>
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <a href="page/visualizar-post.php?id=<?php echo $post['id']; ?>">
                                                            <?php echo htmlspecialchars($post['titulo']); ?>
                                                        </a>
                                                    </h5>
                                                    <p class="card-text">
                                                        <?php 
                                                        $preview = strip_tags($post['conteudo']);
                                                        echo strlen($preview) > 150 ? substr($preview, 0, 150) . "..." : $preview;
                                                        ?>
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-center post-meta">
                                                        <div>
                                                            Por <?php echo htmlspecialchars($post['author_name']); ?><br>
                                                            <small><?php echo date('d/m/Y H:i', strtotime($post['data_criacao'])); ?></small>
                                                        </div>
                                                        <div class="post-stats">
                                                            <span>
                                                                <i class="bi bi-chat-dots"></i><?php echo $post['total_comments']; ?>
                                                            </span>
                                                            <span>
                                                                <i class="bi bi-heart"></i><?php echo $post['total_reactions']; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="empty-state">
                                            <i class="bi bi-journal-text"></i>
                                            <p>Nenhum post encontrado.</p>
                                            <?php if (isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin'] === true): ?>
                                            <a href="page/criar-post.php" class="btn btn-primary btn-novo-post mt-3">
                                                <i class="bi bi-plus-lg"></i> Criar Primeiro Post
                                            </a>
                                            <?php endif; ?>
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
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
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