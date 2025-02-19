<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: autenticacao.php");
    exit;
}

// Configurar fuso horário para Brasil
date_default_timezone_set('America/Sao_Paulo');

$user = $_SESSION['user'];
$is_page = true;

// Incluir header e sidebar primeiro
include_once '../includes/header.php';
include_once '../includes/sidebar.php';

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'sou_digital');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Configurar timezone do MySQL
$conn->query("SET time_zone = '-03:00'");

// Marcar todas como lidas se solicitado
if (isset($_GET['marcar_todas_lidas'])) {
    $stmt = $conn->prepare("UPDATE notificacoes SET lida = TRUE WHERE user_id = ? AND lida = FALSE");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    
    // Redirecionar usando JavaScript para evitar problemas com o header
    echo "<script>window.location.href = 'notificacoes.php';</script>";
    exit;
}

// Buscar todas as notificações do usuário (incluindo as lidas)
$sql = "SELECT * FROM notificacoes WHERE user_id = ? ORDER BY lida ASC, data_criacao DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$todas_notificacoes = $stmt->get_result();

// Contar notificações não lidas
$sql = "SELECT COUNT(*) as total FROM notificacoes WHERE user_id = ? AND lida = FALSE";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$total_result = $stmt->get_result();
$total_nao_lidas = $total_result->fetch_assoc()['total'];

// Função para formatar o tempo decorrido
function tempo_decorrido($data_mysql) {
    $data_anterior = strtotime($data_mysql);
    $agora = time();
    $diferenca = $agora - $data_anterior;
    
    $minutos = round($diferenca / 60);
    $horas = round($diferenca / 3600);
    $dias = round($diferenca / 86400);
    $semanas = round($diferenca / 604800);
    $meses = round($diferenca / 2419200);
    $anos = round($diferenca / 29030400);
    
    if ($diferenca < 60) {
        return "Agora mesmo";
    } elseif ($minutos < 60) {
        return $minutos . " minuto" . ($minutos > 1 ? "s" : "") . " atrás";
    } elseif ($horas < 24) {
        return $horas . " hora" . ($horas > 1 ? "s" : "") . " atrás";
    } elseif ($dias < 7) {
        return $dias . " dia" . ($dias > 1 ? "s" : "") . " atrás";
    } elseif ($semanas < 4) {
        return $semanas . " semana" . ($semanas > 1 ? "s" : "") . " atrás";
    } elseif ($meses < 12) {
        return $meses . " mês" . ($meses > 1 ? "es" : "") . " atrás";
    } else {
        return $anos . " ano" . ($anos > 1 ? "s" : "") . " atrás";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Notificações - Sou + Digital</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/notifications.css" rel="stylesheet">
</head>

<body>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Notificações</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Inicial</a></li>
                    <li class="breadcrumb-item active">Notificações</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="card-title m-0">Todas as Notificações</h5>
                                    <?php if ($total_nao_lidas > 0): ?>
                                        <small class="text-muted">
                                            Você tem <?php echo $total_nao_lidas; ?> notificação<?php echo $total_nao_lidas != 1 ? 'ões' : ''; ?> não lida<?php echo $total_nao_lidas != 1 ? 's' : ''; ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <?php if ($total_nao_lidas > 0): ?>
                                    <a href="?marcar_todas_lidas=1" class="btn btn-primary btn-sm">
                                        <i class="bi bi-check-all"></i> Marcar todas como lidas
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="notification-settings mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notification-sound-toggle">
                                    <label class="form-check-label" for="notification-sound-toggle">Som de notificação</label>
                                </div>
                            </div>

                            <div class="notifications-list">
                                <?php if ($todas_notificacoes && $todas_notificacoes->num_rows > 0): ?>
                                    <?php while($notif = $todas_notificacoes->fetch_assoc()): ?>
                                        <div class="notification-item <?php echo !$notif['lida'] ? 'new' : 'read'; ?>" data-id="<?php echo $notif['id']; ?>">
                                            <div class="icon <?php echo $notif['tipo']; ?>">
                                                <?php
                                                switch($notif['tipo']) {
                                                    case 'aprovacao':
                                                        echo '<i class="bi bi-check-circle"></i>';
                                                        break;
                                                    case 'rejeicao':
                                                        echo '<i class="bi bi-x-circle"></i>';
                                                        break;
                                                    case 'comentario':
                                                        echo '<i class="bi bi-chat-dots"></i>';
                                                        break;
                                                    case 'sistema':
                                                        echo '<i class="bi bi-gear"></i>';
                                                        break;
                                                }
                                                ?>
                                            </div>
                                            <div class="content">
                                                <h4><?php echo htmlspecialchars($notif['titulo']); ?></h4>
                                                <p><?php echo htmlspecialchars($notif['mensagem']); ?></p>
                                                <small data-time="<?php echo $notif['data_criacao']; ?>">
                                                    <?php echo tempo_decorrido($notif['data_criacao']); ?>
                                                </small>
                                            </div>
                                            <div class="actions">
                                                <?php if (!$notif['lida']): ?>
                                                    <button class="mark-as-read" data-id="<?php echo $notif['id']; ?>">
                                                        <i class="bi bi-check2"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($notif['link']): ?>
                                                    <a href="<?php 
                                                        $link = $notif['link'];
                                                        $link = preg_replace('/^page\//', '', $link);
                                                        echo htmlspecialchars($link); 
                                                    ?>" class="btn btn-link">
                                                        <i class="bi bi-arrow-right"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-bell text-muted" style="font-size: 3rem;"></i>
                                        <h5 class="mt-3">Nenhuma notificação</h5>
                                        <p class="text-muted">Você não tem notificações no momento.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Vendor JS Files -->
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/notifications.js"></script>

    <script>
    // Adicionar confirmação ao botão de marcar todas como lidas
    document.addEventListener('DOMContentLoaded', function() {
        const marcarTodasBtn = document.querySelector('a[href="?marcar_todas_lidas=1"]');
        if (marcarTodasBtn) {
            marcarTodasBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Deseja marcar todas as notificações como lidas?')) {
                    window.location.href = this.href;
                }
            });
        }
    });
    </script>
</body>
</html> 