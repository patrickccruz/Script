<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: autenticacao.php");
    exit;
}

$user = $_SESSION['user'];

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'sou_digital');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Buscar posts do usuário
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM blog_reacoes WHERE post_id = p.id) as total_reactions,
        (SELECT COUNT(*) FROM blog_comentarios WHERE post_id = p.id) as total_comments
        FROM blog_posts p 
        WHERE p.user_id = ?
        ORDER BY p.data_criacao DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();

// Função para buscar links de um post
function getPostLinks($conn, $post_id) {
    $links = [];
    $stmt = $conn->prepare("SELECT url, descricao FROM blog_links WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($link = $result->fetch_assoc()) {
        $links[] = $link;
    }
    return $links;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Meus Posts - Sou + Digital</title>
    
    <!-- Inclua seus arquivos CSS aqui -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-top: 1rem;
        }
        .status-badge {
            font-size: 0.875rem;
            padding: 0.35em 0.65em;
            border-radius: 0.25rem;
        }
        .status-pendente {
            background-color: #ffc107;
            color: #000;
        }
        .status-aprovado {
            background-color: #198754;
            color: #fff;
        }
        .status-rejeitado {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>

<body>
    <?php 
    $is_page = true;
    include_once '../includes/header.php'; 
    include_once '../includes/sidebar.php'; 
    ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Meus Posts</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                    <li class="breadcrumb-item active">Meus Posts</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($post = $result->fetch_assoc()): 
                            $post_links = getPostLinks($conn, $post['id']);
                        ?>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($post['titulo']); ?></h5>
                                            <div class="post-meta">
                                                <small>Criado em <?php echo date('d/m/Y H:i', strtotime($post['data_criacao'])); ?></small>
                                                <span class="status-badge status-<?php echo $post['status']; ?>">
                                                    <?php 
                                                    switch($post['status']) {
                                                        case 'pendente':
                                                            echo 'Pendente de Aprovação';
                                                            break;
                                                        case 'aprovado':
                                                            echo 'Aprovado';
                                                            break;
                                                        case 'rejeitado':
                                                            echo 'Rejeitado';
                                                            break;
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if ($post['status'] === 'aprovado'): ?>
                                            <a href="visualizar-post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-eye"></i> Ver Post
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($post['imagem_capa']): ?>
                                        <img src="../<?php echo htmlspecialchars($post['imagem_capa']); ?>" class="preview-image" alt="Imagem do post">
                                    <?php endif; ?>

                                    <?php if ($post['status'] !== 'pendente' && !empty($post['comentario_admin'])): ?>
                                        <div class="alert <?php echo $post['status'] === 'aprovado' ? 'alert-success' : 'alert-danger'; ?> mt-3">
                                            <h6 class="alert-heading">Feedback do Administrador:</h6>
                                            <?php echo nl2br(htmlspecialchars($post['comentario_admin'])); ?>
                                            <?php if ($post['status'] === 'aprovado'): ?>
                                                <div class="mt-2">
                                                    <small>Aprovado em: <?php echo date('d/m/Y H:i', strtotime($post['data_aprovacao'])); ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($post['status'] === 'pendente'): ?>
                                        <div class="alert alert-info mt-3">
                                            <i class="bi bi-info-circle"></i>
                                            Seu post está aguardando aprovação do administrador. Você será notificado quando houver uma atualização.
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($post_links)): ?>
                                        <div class="mt-3">
                                            <h6>Links Relacionados:</h6>
                                            <ul class="list-unstyled">
                                                <?php foreach ($post_links as $link): ?>
                                                    <li>
                                                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank">
                                                            <i class="bi bi-link-45deg"></i>
                                                            <?php echo htmlspecialchars($link['descricao']); ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($post['status'] === 'aprovado'): ?>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-heart"></i> <?php echo $post['total_reactions']; ?> reações
                                                <i class="bi bi-chat-dots ms-3"></i> <?php echo $post['total_comments']; ?> comentários
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center py-5">
                                    <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">Você ainda não criou nenhum post</h5>
                                    <p class="text-muted">Que tal começar agora?</p>
                                    <a href="criar-post.php" class="btn btn-primary">
                                        <i class="bi bi-plus-lg"></i> Criar Novo Post
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <!-- Vendor JS Files -->
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html> 