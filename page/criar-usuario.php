<?php
session_start();

// Se já estiver logado, redireciona para a página inicial
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    header("Location: ../index.php");
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        $error_message = "Por favor, preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email inválido.";
    } else {
        // Conexão com o banco de dados
        $conn = new mysqli('localhost', 'root', '', 'sou_digital');
        
        if ($conn->connect_error) {
            $error_message = "Erro de conexão com o banco de dados: " . $conn->connect_error;
        } else {
            // Verificar se usuário ou email já existem
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            if (!$stmt) {
                $error_message = "Erro na preparação da consulta: " . $conn->error;
            } else {
                $stmt->bind_param("ss", $username, $email);
                if (!$stmt->execute()) {
                    $error_message = "Erro na execução da consulta: " . $stmt->error;
                } else {
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $error_message = "Usuário ou email já cadastrado.";
                    } else {
                        // Criar novo usuário
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $insert_stmt = $conn->prepare("INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)");
                        
                        if (!$insert_stmt) {
                            $error_message = "Erro na preparação da inserção: " . $conn->error;
                        } else {
                            $insert_stmt->bind_param("ssss", $name, $username, $email, $hashed_password);
                            
                            if ($insert_stmt->execute()) {
                                $_SESSION['register_success'] = true;
                                header("Location: autenticacao.php");
                                exit;
                            } else {
                                $error_message = "Erro ao criar conta: " . $insert_stmt->error;
                            }
                            $insert_stmt->close();
                        }
                    }
                }
                $stmt->close();
            }
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Criar Conta - Sou + Digital</title>
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
</head>

<body>
  <main>
    <div class="container">
      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="../index.php" class="logo d-flex align-items-center w-auto">
                  <img src="../assets/img/Ico_geral.png" alt="Logo">
                  <span class="d-none d-lg-block">Sou + Digital</span>
                </a>
              </div>

              <div class="card mb-3">
                <div class="card-body">
                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Criar uma Conta</h5>
                    <p class="text-center small">Digite seus dados pessoais para criar uma conta</p>
                  </div>

                  <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                      <?php echo htmlspecialchars($error_message); ?>
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                  <?php endif; ?>

                  <form class="row g-3 needs-validation" method="POST" novalidate>
                    <div class="col-12">
                      <label for="name" class="form-label">Nome Completo</label>
                      <input type="text" name="name" class="form-control" id="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                      <div class="invalid-feedback">Por favor, digite seu nome!</div>
                    </div>

                    <div class="col-12">
                      <label for="username" class="form-label">Nome de Usuário</label>
                      <div class="input-group has-validation">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input type="text" name="username" class="form-control" id="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        <div class="invalid-feedback">Por favor, escolha um nome de usuário!</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="email" class="form-label">Email</label>
                      <input type="email" name="email" class="form-control" id="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                      <div class="invalid-feedback">Por favor, digite um email válido!</div>
                    </div>

                    <div class="col-12">
                      <label for="password" class="form-label">Senha</label>
                      <input type="password" name="password" class="form-control" id="password" required>
                      <div class="invalid-feedback">Por favor, digite uma senha!</div>
                    </div>

                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit">Criar Conta</button>
                    </div>

                    <div class="col-12">
                      <p class="small mb-0">Já tem uma conta? <a href="autenticacao.php">Faça login</a></p>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>

  <?php include_once '../includes/footer.php'; ?>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

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
    // Validação do formulário
    (function () {
      'use strict'

      // Buscar todos os formulários que precisam de validação
      var forms = document.querySelectorAll('.needs-validation')

      // Loop sobre eles e prevenir submissão se inválidos
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault()
              event.stopPropagation()
            }

            form.classList.add('was-validated')
          }, false)
        })
    })()

    // Validação adicional de email
    document.getElementById('email').addEventListener('input', function() {
      var email = this.value;
      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      
      if (!emailRegex.test(email)) {
        this.setCustomValidity('Por favor, insira um email válido');
      } else {
        this.setCustomValidity('');
      }
    });
  </script>
</body>
</html>
