<?php
include '../db.php';

if (!isset($conn) || !$conn) {
  die("Falha na conexão: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = $_POST['id'];
  $name = $_POST['name'];
  $email = $_POST['email'];

  $sql = "UPDATE users SET name=?, email=? WHERE id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssi", $name, $email, $id);

  if ($stmt->execute()) {
    header("Location: manage_users.php");
  } else {
    echo "Erro ao atualizar usuário: " . $conn->error;
  }

  $stmt->close();
} else {
  $id = $_GET['id'];
  $sql = "SELECT id, name, email FROM users WHERE id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Editar Usuário</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="../assets/img/favicon.png" rel="icon">
  <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
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
</head>
<body>

<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: ./user-login.php");
    exit;
}
// Supondo que os dados do usuário estejam armazenados na sessão
if (isset($_SESSION['user'])) {
  $userSession = $_SESSION['user'];
  // Log para informar o que está salvo na sessão
  error_log("Dados do usuário na sessão: " . print_r($userSession, true));
} else {
  $userSession = ['id' => 0, 'name' => 'Usuário', 'username' => 'username'];
}
?>

<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

  <div class="d-flex align-items-center justify-content-between">
    <a href="../index.php" class="logo d-flex align-items-center">
      <img src="../assets/img/Icon geral.png" alt="">
      <span class="d-none d-lg-block">Script</span>
    </a>
    <i class="bi bi-list toggle-sidebar-btn"></i>
  </div><!-- End Logo -->

  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">

      <li class="nav-item dropdown pe-3">

        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
        <img src="../assets/img/sem_foto.png" alt="Profile" class="rounded-circle">

          <span class="d-none d-md-block dropdown-toggle ps-2">
            <?php echo htmlspecialchars($userSession['name'], ENT_QUOTES, 'UTF-8'); ?>
          </span>
        </a><!-- End Profile Iamge Icon -->

        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
          <li class="dropdown-header">
            <h6>Nome: <?php echo htmlspecialchars($userSession['name'], ENT_QUOTES, 'UTF-8'); ?></h6>
            <span> Usuario: <?php echo htmlspecialchars($userSession['username'], ENT_QUOTES, 'UTF-8'); ?></span>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>

          <li>
            <a class="dropdown-item d-flex align-items-center" href="../page/profile.php">
              <i class="bi bi-person"></i>
              <span>Meu Perfil</span>
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="../page/manage_users.php">
              <i class="bi bi-gear"></i>
              <span>Administração</span>
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>

          <li>
            <a class="dropdown-item d-flex align-items-center" href="page/logout.php">
              <i class="bi bi-box-arrow-right"></i>
              <span>Deslogar</span>
            </a>
          </li>

        </ul><!-- End Profile Dropdown Items -->
      </li><!-- End Profile Nav -->

    </ul>
  </nav><!-- End Icons Navigation -->
</header><!-- End Header -->

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

<ul class="sidebar-nav" id="sidebar-nav">

  <li class="nav-item">
    <a class="nav-link " href="../index.php">
      <i class="bi bi-journal-text"></i>
      <span>Gerador Script</span>
    </a>
  </li><!-- End Dashboard Nav -->
  <li class="nav-item">
        <a class="nav-link" href="../page/reembolso.php">
          <i class="bx bx-money"></i>
          <span>Solicitação de reembolso</span>
        </a>
      </li><!-- End Reembolso Nav -->
</ul>
</aside><!-- End Sidebar-->
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Editar Usuário</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Inicial</a></li>
        <li class="breadcrumb-item">Administração</li>
        <li class="breadcrumb-item active">Editar Usuário</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Formulário de Edição</h5>

            <form method="post" action="edit_user.php">
              <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
              <div class="mb-3">
                <label for="name" class="form-label">Nome</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
              </div>
              <button type="submit" class="btn btn-primary">Salvar</button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </section>
</main><!-- End #main -->


  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>Patrick C Cruz</span></strong>. Todos os direitos Reservado
    </div>
    <div class="credits">
      Feito pelo <a href="https://www.linkedin.com/in/patrick-da-costa-cruz-08493212a/" target="_blank">Patrick C
        Cruz</a>
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../script.js"></script>
  <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>

  <!-- Template Main JS File -->
  <script src="../assets/js/main.js"></script>

</body>
</html>
