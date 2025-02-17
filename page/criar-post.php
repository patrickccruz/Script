<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: autenticacao.php");
    exit;
}

if (!isset($_SESSION['user']['is_admin']) || $_SESSION['user']['is_admin'] != true) {
    header("Location: ../index.php");
    exit;
}

$user = $_SESSION['user'];

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'sou_digital');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['conteudo'] ?? '');
    $links = isset($_POST['links']) ? $_POST['links'] : [];
    $links_descricao = isset($_POST['links_descricao']) ? $_POST['links_descricao'] : [];

    if (empty($titulo) || empty($conteudo)) {
        $_SESSION['error'] = "Por favor, preencha todos os campos obrigatórios.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Upload da imagem
    $imagem_capa = '';
    if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] == 0) {
        $upload_dir = '../uploads/blog/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['imagem_capa']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $allowed_extensions)) {
            $_SESSION['error'] = "Tipo de arquivo não permitido. Use apenas imagens (JPG, PNG, GIF).";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['imagem_capa']['tmp_name'], $upload_path)) {
            $imagem_capa = 'uploads/blog/' . $new_filename;
        } else {
            $_SESSION['error'] = "Erro ao fazer upload da imagem.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } else {
        $_SESSION['error'] = "Por favor, selecione uma imagem de capa.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Inserir post
    $stmt = $conn->prepare("INSERT INTO blog_posts (user_id, titulo, conteudo, imagem_capa) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user['id'], $titulo, $conteudo, $imagem_capa);
    
    if ($stmt->execute()) {
        $post_id = $conn->insert_id;

        // Inserir links
        if (!empty($links)) {
            $stmt = $conn->prepare("INSERT INTO blog_links (post_id, url, descricao) VALUES (?, ?, ?)");
            foreach ($links as $i => $url) {
                if (!empty($url) && !empty($links_descricao[$i])) {
                    $stmt->bind_param("iss", $post_id, $url, $links_descricao[$i]);
                    $stmt->execute();
                }
            }
        }

        $_SESSION['success'] = "Post criado com sucesso!";
        header("Location: visualizar-post.php?id=" . $post_id);
        exit;
    } else {
        $_SESSION['error'] = "Erro ao criar o post: " . $conn->error;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Nova Publicação - Sou + Digital</title>
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
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
            display: none;
            margin-top: 1rem;
        }
        .link-group {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        #editor {
            height: 500px;
            background: white;
        }
        .ql-video {
            width: 100%;
            height: 400px;
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
                                <h1>Nova Publicação</h1>
                                <nav>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                                        <li class="breadcrumb-item active">Nova Publicação</li>
                                    </ol>
                                </nav>
                            </div>

                            <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" required>
                                </div>

                                <div class="mb-3">
                                    <label for="imagem_capa" class="form-label">Imagem de Capa</label>
                                    <input type="file" class="form-control" id="imagem_capa" name="imagem_capa" accept="image/*" required onchange="previewImage(this)">
                                    <img id="preview" class="preview-image">
                                </div>

                                <div class="mb-3">
                                    <label for="conteudo" class="form-label">Conteúdo</label>
                                    <div id="editor"></div>
                                    <input type="hidden" id="conteudo" name="conteudo">
                                </div>

                                <div id="links-container">
                                    <h4 class="mb-3">Links Relacionados</h4>
                                    <div class="link-group">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">URL do Link</label>
                                                <input type="url" class="form-control" name="links[]">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Descrição do Link</label>
                                                <input type="text" class="form-control" name="links_descricao[]">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-secondary mb-3" onclick="adicionarLink()">
                                    <i class="bi bi-plus-circle"></i> Adicionar Link
                                </button>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">Publicar</button>
                                    <a href="../index.php" class="btn btn-secondary">Cancelar</a>
                                </div>
                            </form>
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
    <script src="../assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../assets/js/main.js"></script>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Inicialização do Quill
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            },
            placeholder: 'Escreva seu conteúdo aqui...'
        });

        // Quando o formulário for enviado, atualiza o campo hidden com o conteúdo HTML
        document.querySelector('form').addEventListener('submit', function(e) {
            var content = quill.root.innerHTML;
            if (!content || content.trim() === '') {
                e.preventDefault();
                alert('Por favor, preencha o conteúdo do post.');
                return;
            }
            document.getElementById('conteudo').value = content;
        });

        // Handler para upload de imagens
        quill.getModule('toolbar').addHandler('image', function() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = async function() {
                const file = input.files[0];
                if (file) {
                    try {
                        const formData = new FormData();
                        formData.append('file', file);

                        const response = await fetch('../upload.php', {
                            method: 'POST',
                            body: formData
                        });

                        if (!response.ok) throw new Error('Erro no upload');

                        const data = await response.json();
                        const range = quill.getSelection(true);
                        quill.insertEmbed(range.index, 'image', data.location);
                    } catch (error) {
                        alert('Erro ao fazer upload da imagem: ' + error.message);
                    }
                }
            };
        });

        function adicionarLink() {
            const container = document.getElementById('links-container');
            const linkGroup = document.createElement('div');
            linkGroup.className = 'link-group';
            linkGroup.innerHTML = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">URL do Link</label>
                        <input type="url" class="form-control" name="links[]">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Descrição do Link</label>
                        <input type="text" class="form-control" name="links_descricao[]">
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                    <i class="bi bi-trash"></i> Remover Link
                </button>
            `;
            container.appendChild(linkGroup);
        }
    </script>
</body>
</html> 