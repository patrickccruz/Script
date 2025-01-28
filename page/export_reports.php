<?php
  session_start();
  if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: ../page/user-login.php");
    exit;
  }
  
  $conn = new mysqli('localhost', 'root', '', 'sou_digital');
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Mesma lógica de filtro do view-reports.php
  $where = [];
  if (!empty($_GET['data_chamado'])) {
    $where[] = "reports.data_chamado = '{$_GET['data_chamado']}'";
  }
  if (!empty($_GET['cliente'])) {  
    $where[] = "reports.cliente LIKE '%{$_GET['cliente']}%'";
  }
  if (!empty($_GET['user_id'])) {
    $where[] = "reports.user_id = {$_GET['user_id']}";
  }
  $whereSQL = implode(' AND ', $where);

  $reports = $conn->query("SELECT reports.*, users.name as user_name FROM reports JOIN users ON reports.user_id = users.id ".($whereSQL ? "WHERE $whereSQL" : ""));

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=relatorios.csv');

  $output = fopen('php://output', 'w');
  fputcsv($output, array('Data Chamado', 'Número Chamado', 'Cliente', 'Nome Informante', 'Usuário', 'Quantidade Patrimônios', 'KM Inicial', 'KM Final', 'Hora Chegada', 'Hora Saída', 'Endereço Partida', 'Endereço Chegada', 'Informações Adicionais'));

  while ($row = $reports->fetch_assoc()) {
    fputcsv($output, $row);
  }

  $reports->free();
  $conn->close();
?> 