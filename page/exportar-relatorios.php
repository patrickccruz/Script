<?php
  session_start();
  if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("Location: ../page/autenticacao.php");
    exit;
  }
  
  require __DIR__ . '/../vendor/autoload.php'; // Ajustando o caminho do autoload

  use PhpOffice\PhpSpreadsheet\Spreadsheet;
  use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
  use PhpOffice\PhpSpreadsheet\Style\Alignment;
  use PhpOffice\PhpSpreadsheet\Style\Border;
  use PhpOffice\PhpSpreadsheet\Style\Fill;

  $conn = new mysqli('localhost', 'root', '', 'sou_digital');
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Mesma lógica de filtro do visualizar-relatorios.php
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

  $reports = $conn->query("SELECT 
    reports.data_chamado,
    reports.numero_chamado,
    reports.cliente,
    reports.nome_informante,
    users.name as tecnico,
    reports.quantidade_patrimonios,
    reports.km_inicial,
    reports.km_final,
    reports.hora_chegada,
    reports.hora_saida,
    reports.endereco_partida,
    reports.endereco_chegada,
    reports.informacoes_adicionais
    FROM reports 
    JOIN users ON reports.user_id = users.id 
    ".($whereSQL ? "WHERE $whereSQL" : "")."
    ORDER BY reports.data_chamado DESC");

  // Criar nova planilha
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();

  // Definir cabeçalhos
  $headers = [
    'A1' => 'Data do Chamado',
    'B1' => 'Número do Chamado',
    'C1' => 'Cliente',
    'D1' => 'Nome do Informante',
    'E1' => 'Técnico Responsável',
    'F1' => 'Quantidade de Patrimônios',
    'G1' => 'KM Inicial',
    'H1' => 'KM Final',
    'I1' => 'Total KM',
    'J1' => 'Hora de Chegada',
    'K1' => 'Hora de Saída',
    'L1' => 'Tempo Total',
    'M1' => 'Endereço de Partida',
    'N1' => 'Endereço de Chegada',
    'O1' => 'Informações Adicionais'
  ];

  // Aplicar cabeçalhos e estilo
  foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
  }

  // Estilo para cabeçalhos
  $headerStyle = [
    'font' => [
      'bold' => true,
      'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
      'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
      'color' => ['rgb' => '4F81BD']
    ],
    'borders' => [
      'allBorders' => [
        'borderStyle' => Border::BORDER_THIN
      ]
    ],
    'alignment' => [
      'horizontal' => Alignment::HORIZONTAL_CENTER,
      'vertical' => Alignment::VERTICAL_CENTER
    ]
  ];

  $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);

  // Ajustar largura das colunas
  $sheet->getColumnDimension('A')->setWidth(15); // Data
  $sheet->getColumnDimension('B')->setWidth(20); // Número
  $sheet->getColumnDimension('C')->setWidth(25); // Cliente
  $sheet->getColumnDimension('D')->setWidth(25); // Informante
  $sheet->getColumnDimension('E')->setWidth(25); // Técnico
  $sheet->getColumnDimension('F')->setWidth(15); // Quantidade
  $sheet->getColumnDimension('G')->setWidth(12); // KM Inicial
  $sheet->getColumnDimension('H')->setWidth(12); // KM Final
  $sheet->getColumnDimension('I')->setWidth(12); // Total KM
  $sheet->getColumnDimension('J')->setWidth(15); // Hora Chegada
  $sheet->getColumnDimension('K')->setWidth(15); // Hora Saída
  $sheet->getColumnDimension('L')->setWidth(15); // Tempo Total
  $sheet->getColumnDimension('M')->setWidth(35); // Endereço Partida
  $sheet->getColumnDimension('N')->setWidth(35); // Endereço Chegada
  $sheet->getColumnDimension('O')->setWidth(40); // Informações

  // Inserir dados
  $row = 2;
  while ($data = $reports->fetch_assoc()) {
    // Formatar data
    $data_chamado = date('d/m/Y', strtotime($data['data_chamado']));
    
    // Calcular total de KM
    $km_total = $data['km_final'] - $data['km_inicial'];
    
    // Calcular tempo total
    $hora_chegada = strtotime($data['hora_chegada']);
    $hora_saida = strtotime($data['hora_saida']);
    $tempo_total = $hora_saida - $hora_chegada;
    $tempo_formatado = sprintf("%02d:%02d", 
      floor($tempo_total / 3600),
      floor(($tempo_total % 3600) / 60)
    );

    // Inserir linha de dados
    $sheet->setCellValue('A'.$row, $data_chamado);
    $sheet->setCellValue('B'.$row, $data['numero_chamado']);
    $sheet->setCellValue('C'.$row, $data['cliente']);
    $sheet->setCellValue('D'.$row, $data['nome_informante']);
    $sheet->setCellValue('E'.$row, $data['tecnico']);
    $sheet->setCellValue('F'.$row, $data['quantidade_patrimonios']);
    $sheet->setCellValue('G'.$row, $data['km_inicial']);
    $sheet->setCellValue('H'.$row, $data['km_final']);
    $sheet->setCellValue('I'.$row, $km_total);
    $sheet->setCellValue('J'.$row, $data['hora_chegada']);
    $sheet->setCellValue('K'.$row, $data['hora_saida']);
    $sheet->setCellValue('L'.$row, $tempo_formatado);
    $sheet->setCellValue('M'.$row, $data['endereco_partida']);
    $sheet->setCellValue('N'.$row, $data['endereco_chegada']);
    $sheet->setCellValue('O'.$row, $data['informacoes_adicionais']);

    $row++;
  }

  // Estilo para as células de dados
  $dataStyle = [
    'borders' => [
      'allBorders' => [
        'borderStyle' => Border::BORDER_THIN
      ]
    ],
    'alignment' => [
      'vertical' => Alignment::VERTICAL_CENTER
    ]
  ];

  $sheet->getStyle('A2:O'.($row-1))->applyFromArray($dataStyle);

  // Alternar cores das linhas
  for ($i = 2; $i < $row; $i++) {
    if ($i % 2 == 0) {
      $sheet->getStyle('A'.$i.':O'.$i)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('F5F5F5');
    }
  }

  // Configurar cabeçalho do arquivo
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment;filename="relatorio_atendimentos_'.date('Y-m-d').'.xlsx"');
  header('Cache-Control: max-age=0');

  // Criar o arquivo Excel
  $writer = new Xlsx($spreadsheet);
  $writer->save('php://output');

  $reports->free();
  $conn->close();
  exit;
?> 