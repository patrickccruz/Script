<?php
if (!isset($_SESSION)) {
    session_start();
}

// Determinar se estamos em uma subpasta ou na raiz
$isSubfolder = strpos($_SERVER['PHP_SELF'], '/page/') !== false;
$basePath = $isSubfolder ? '../' : '';
?>
<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">
    <!-- Categoria: Ferramentas -->
    <li class="nav-heading">Ferramentas</li>
    <li class="nav-item">
      <a class="nav-link" href="<?php echo $basePath; ?>index.php">
        <i class="bi bi-journal-text"></i>
        <span>Gerador Script</span>
      </a>
    </li>

    <!-- Categoria: Reembolsos -->
    <li class="nav-heading">Reembolsos</li>
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#reembolsos-nav" data-bs-toggle="collapse" href="#">
        <i class="bx bx-money"></i><span>Gestão de Reembolsos</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="reembolsos-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="<?php echo $basePath; ?>page/reembolso.php">
            <i class="bi bi-circle"></i><span>Solicitar Reembolso</span>
          </a>
        </li>
        <li>
          <a href="<?php echo $basePath; ?>page/my-reembolsos.php">
            <i class="bi bi-circle"></i><span>Meus Reembolsos</span>
          </a>
        </li>
      </ul>
    </li>

    <?php if (isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin'] === true): ?>
    <!-- Categoria: Administração -->
    <li class="nav-heading">Administração</li>
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#admin-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-gear"></i><span>Configurações</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="admin-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="<?php echo $basePath; ?>page/manage_users.php">
            <i class="bi bi-circle"></i><span>Gerenciar Usuários</span>
          </a>
        </li>
      </ul>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#relatorios-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-file-earmark-text"></i><span>Relatórios</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="relatorios-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="<?php echo $basePath; ?>page/all-reembolsos.php">
            <i class="bi bi-circle"></i><span>Todos os Reembolsos</span>
          </a>
        </li>
      </ul>
    </li>
    <?php endif; ?>
  </ul>
</aside><!-- End Sidebar--> 