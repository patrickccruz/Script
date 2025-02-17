<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: autenticacao.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$post_id = intval($_GET['id']);
$user = $_SESSION['user'];

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'sou_digital');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Buscar dados do post
$query = "SELECT p.*, u.name as autor_nome 
          FROM blog_posts p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    header("Location: ../index.php");
    exit;
}

// Processar nova reação
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reacao'])) {
    $tipo_reacao = $_POST['reacao'];
    
    // Verificar se já existe reação deste usuário
    $stmt = $conn->prepare("SELECT id FROM blog_reacoes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Atualizar reação existente
        $stmt = $conn->prepare("UPDATE blog_reacoes SET tipo_reacao = ? WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("sii", $tipo_reacao, $post_id, $user['id']);
    } else {
        // Inserir nova reação
        $stmt = $conn->prepare("INSERT INTO blog_reacoes (post_id, user_id, tipo_reacao) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user['id'], $tipo_reacao);
    }
    $stmt->execute();
    header("Location: visualizar-post.php?id=" . $post_id);
    exit;
}

// Processar novo comentário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comentario'])) {
    $comentario = trim($_POST['comentario']);
    if (!empty($comentario)) {
        $stmt = $conn->prepare("INSERT INTO blog_comentarios (post_id, user_id, comentario) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user['id'], $comentario);
        $stmt->execute();
        header("Location: visualizar-post.php?id=" . $post_id);
        exit;
    }
}

// Buscar reações do post
$query = "SELECT tipo_reacao, COUNT(*) as total 
          FROM blog_reacoes 
          WHERE post_id = ? 
          GROUP BY tipo_reacao";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$reacoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Buscar reação do usuário atual
$stmt = $conn->prepare("SELECT tipo_reacao FROM blog_reacoes WHERE post_id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user['id']);
$stmt->execute();
$reacao_usuario = $stmt->get_result()->fetch_assoc();

// Buscar comentários do post
$query = "SELECT c.*, u.name as autor_nome 
          FROM blog_comentarios c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.post_id = ? 
          ORDER BY c.data_criacao DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comentarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Buscar links do post
$query = "SELECT * FROM blog_links WHERE post_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$links = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title><?php echo htmlspecialchars($post['titulo']); ?> - Sou + Digital</title>
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
        .post-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .reaction-btn {
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }
        .reaction-btn:hover, .reaction-btn.active {
            transform: scale(1.1);
            background-color: #e9ecef;
        }
        .comment-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #dee2e6;
        }
        .comment {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            background-color: #f8f9fa;
        }
        .links-section {
            margin-top: 2rem;
            padding: 1rem;
            border-radius: 10px;
            background-color: #f8f9fa;
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
                                <h1><?php echo htmlspecialchars($post['titulo']); ?></h1>
                                <nav>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                                        <li class="breadcrumb-item active">Visualizar Post</li>
                                    </ol>
                                </nav>
                                <?php if (isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin'] === true): ?>
                                <div class="mt-3">
                                    <form method="POST" action="deletar-post.php" onsubmit="return confirm('Tem certeza que deseja excluir este post? Esta ação não pode ser desfeita.');" class="d-inline">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-trash"></i> Excluir Post
                                        </button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="post-meta text-muted mb-4">
                                <p>
                                    Por <?php echo htmlspecialchars($post['autor_nome']); ?> em 
                                    <?php echo date('d/m/Y H:i', strtotime($post['data_criacao'])); ?>
                                </p>
                            </div>

                            <?php if ($post['imagem_capa']): ?>
                            <img src="../<?php echo htmlspecialchars($post['imagem_capa']); ?>" alt="<?php echo htmlspecialchars($post['titulo']); ?>" class="post-image">
                            <?php endif; ?>

                            <div class="post-content">
                                <?php echo $post['conteudo']; ?>
                            </div>

                            <?php if (!empty($links)): ?>
                            <div class="links-section">
                                <h4>Links Relacionados:</h4>
                                <ul>
                                    <?php foreach ($links as $link): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank">
                                            <?php echo htmlspecialchars($link['descricao']); ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <div class="reactions-section mt-4">
                                <h4>Reações:</h4>
                                <form method="POST" class="d-flex gap-2 mb-3">
                                    <button type="submit" name="reacao" value="curtir" class="reaction-btn <?php echo ($reacao_usuario['tipo_reacao'] ?? '') == 'curtir' ? 'active' : ''; ?>">
                                        👍 Curtir
                                    </button>
                                    <button type="submit" name="reacao" value="amar" class="reaction-btn <?php echo ($reacao_usuario['tipo_reacao'] ?? '') == 'amar' ? 'active' : ''; ?>">
                                        ❤️ Amar
                                    </button>
                                    <button type="submit" name="reacao" value="rir" class="reaction-btn <?php echo ($reacao_usuario['tipo_reacao'] ?? '') == 'rir' ? 'active' : ''; ?>">
                                        😄 Rir
                                    </button>
                                    <button type="submit" name="reacao" value="surpreso" class="reaction-btn <?php echo ($reacao_usuario['tipo_reacao'] ?? '') == 'surpreso' ? 'active' : ''; ?>">
                                        😮 Surpreso
                                    </button>
                                    <button type="submit" name="reacao" value="triste" class="reaction-btn <?php echo ($reacao_usuario['tipo_reacao'] ?? '') == 'triste' ? 'active' : ''; ?>">
                                        😢 Triste
                                    </button>
                                    <button type="submit" name="reacao" value="bravo" class="reaction-btn <?php echo ($reacao_usuario['tipo_reacao'] ?? '') == 'bravo' ? 'active' : ''; ?>">
                                        😠 Bravo
                                    </button>
                                </form>

                                <div class="reactions-summary">
                                    <?php foreach ($reacoes as $reacao): ?>
                                        <span class="badge bg-primary me-2">
                                            <?php 
                                            $emoji = [
                                                'curtir' => '👍',
                                                'amar' => '❤️',
                                                'rir' => '😄',
                                                'surpreso' => '😮',
                                                'triste' => '😢',
                                                'bravo' => '😠'
                                            ][$reacao['tipo_reacao']];
                                            echo $emoji . ' ' . $reacao['total'];
                                            ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="comment-section">
                                <h4>Comentários:</h4>
                                
                                <form method="POST" class="mb-4">
                                    <div class="form-group">
                                        <textarea name="comentario" class="form-control" rows="3" placeholder="Deixe seu comentário..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-2">Comentar</button>
                                </form>

                                <?php foreach ($comentarios as $comentario): ?>
                                <div class="comment">
                                    <div class="d-flex justify-content-between">
                                        <h6><?php echo htmlspecialchars($comentario['autor_nome']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($comentario['data_criacao'])); ?>
                                        </small>
                                    </div>
                                    <p><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

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
</body>
</html> 