<?php
require_once 'validarSessao.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Manutenção Industrial</title>
    
    <!-- AdminLTE 3 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.0/css/dataTables.bootstrap5.min.css">
    
    <style>
        .content-wrapper {
            background-color: #f4f6f9;
        }
        .main-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar-dark-primary {
            background-color: #343a40;
        }
        .nav-sidebar .nav-link {
            color: #c2c7d0;
        }
        .nav-sidebar .nav-link:hover {
            color: #fff;
            background-color: #495057;
        }
        .nav-sidebar .nav-link.active {
            background-color: #007bff;
            color: #fff;
        }
        
        /* ===== CONFIGURAÇÕES BÁSICAS DA SIDEBAR ===== */
        .main-sidebar {
            width: 280px !important;
            overflow: hidden;
            z-index: 1051 !important;
        }
        
        .content-wrapper {
            margin-left: 280px !important;
        }
        
        /* ===== BRAND-LINK (TÍTULO) ===== */
        .brand-link {
            width: 280px !important;
            transition: width 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            overflow: hidden;
            box-sizing: border-box;
            position: relative;
            padding: 0 0.2rem 0 0.7rem;
        }
        
        .brand-link .brand-image {
            margin-right: 0.5rem;
            flex-shrink: 0;
        }
        
        .brand-link .brand-text {
            flex: 1;
            font-size: 1.05rem;
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
            line-height: 1.1;
            display: block;
            word-break: break-word;
            max-height: 2.3em;
        }
        
        .sidebar-toggle-btn {
            margin-left: auto;
            color: inherit;
            background: transparent;
            border: none;
            padding: 0 12px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            height: 100%;
            z-index: 1101;
        }
        
        /* ===== MENU NAVEGAÇÃO ===== */
        .nav-sidebar {
            width: 100%;
            overflow: hidden;
        }
        
        .nav-sidebar .nav-link {
            padding: 0.75rem 1rem;
            min-height: 45px;
            display: flex;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
        }
        
        .nav-sidebar .nav-link p {
            font-size: 0.9rem;
            line-height: 1.2;
            margin-bottom: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
            flex: 1;
            min-width: 0;
        }
        
        .nav-sidebar .nav-icon {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        
        /* ===== SIDEBAR RECOLHIDA ===== */
        .sidebar-collapse .main-sidebar {
            width: 4.6rem !important;
        }
        
        .sidebar-collapse .content-wrapper {
            margin-left: 4.6rem !important;
        }
        
        .sidebar-collapse .brand-link {
            width: 4.6rem !important;
            justify-content: center;
            padding: 0.5rem 0 0.5rem 0;
        }
        
        .sidebar-collapse .brand-link .brand-text {
            display: none;
        }
        
        .sidebar-collapse .nav-sidebar .nav-link p {
            display: none;
        }
        
        .sidebar-collapse .nav-sidebar .nav-link {
            padding: 0.75rem 0.5rem;
            justify-content: center;
        }
        
        .sidebar-collapse .nav-sidebar .nav-icon {
            margin-right: 0;
            width: 2.2rem;
            display: block;
        }
        
        .sidebar-collapse .nav-header {
            display: none;
        }
        
        .sidebar-collapse .sidebar-toggle-btn {
            margin-left: 0;
            justify-content: center;
            width: 100%;
        }
        
        .sidebar-collapse .brand-link .brand-image {
            margin-right: 0;
        }
        
        .sidebar-collapse .brand-link .brand-text {
            display: none;
        }
        
        body.sidebar-collapse .main-sidebar:hover {
            width: 280px !important;
            z-index: 1052 !important;
        }
        
        body.sidebar-collapse .main-sidebar:hover ~ .content-wrapper {
            margin-left: 280px !important;
        }
        
        body.sidebar-collapse .main-sidebar:hover .brand-link {
            width: 280px !important;
            justify-content: flex-start;
            padding: 0 0.5rem 0 1rem;
        }
        
        body.sidebar-collapse .main-sidebar:hover .brand-link .brand-image {
            margin-right: 0.5rem;
        }
        
        body.sidebar-collapse .main-sidebar:hover .brand-link .brand-text {
            display: inline;
            margin-left: 0;
        }
        
        body.sidebar-collapse .main-sidebar:hover .sidebar-toggle-btn {
            margin-left: auto;
            width: auto;
            justify-content: flex-end;
        }
        
        body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link p {
            display: block;
        }
        
        body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link {
            padding: 0.75rem 1rem;
            justify-content: flex-start;
        }
        
        body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-icon {
            margin-right: 0.5rem;
            width: 20px;
        }
        
        body.sidebar-collapse .main-sidebar:hover .nav-header {
            display: block;
        }
        
        /* ===== TOOLTIP PARA SIDEBAR RECOLHIDA ===== */
        .sidebar-collapse .nav-sidebar .nav-link {
            position: relative;
        }
        
        .sidebar-collapse .nav-sidebar .nav-link:hover::after {
            content: attr(title);
            position: absolute;
            left: 110%;
            top: 50%;
            transform: translateY(-50%);
            background: #333;
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            font-size: 0.9rem;
            white-space: nowrap;
            z-index: 2000;
            margin-left: 0.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            pointer-events: none;
        }
        
        /* Remove tooltip quando expandido */
        body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link:hover::after {
            display: none !important;
        }
        
        /* ===== BOTÃO DE TOGGLE ===== */
        .main-header .navbar-nav .nav-link[data-widget="pushmenu"] {
            z-index: 1100;
        }
        
        /* ===== RESPONSIVO ===== */
        @media (max-width: 768px) {
            .main-sidebar {
                width: 250px !important;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .content-wrapper {
                margin-left: 0 !important;
            }
            .brand-link {
                width: 250px !important;
            }
            
            /* Menu aberto em mobile */
            body.sidebar-open .main-sidebar {
                transform: translateX(0);
            }
            
            /* Overlay para fechar menu */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1050;
            }
            
            body.sidebar-open .sidebar-overlay {
                display: block;
            }
            
            /* Botão de menu no header */
            .navbar-nav .nav-link[data-widget="pushmenu"] {
                display: block !important;
            }
        }
        
        /* Esconder botão de menu em desktop */
        @media (min-width: 769px) {
            .navbar-nav .nav-link[data-widget="pushmenu"] {
                display: none !important;
            }
        }
        
        /* ===== TRANSIÇÕES ===== */
        .main-sidebar, .content-wrapper, .brand-link {
            transition: width 0.2s ease, margin-left 0.2s ease;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?php echo $_SESSION['nome']; ?>
                        <span class="badge badge-<?php echo $_SESSION['nivel_acesso'] === 'admin' ? 'danger' : 'info'; ?> ml-1">
                            <?php echo ucfirst($_SESSION['nivel_acesso']); ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i>Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <div class="brand-link" style="display: flex; align-items: center; justify-content: flex-start; position: relative; width: 100%;">
                <i class="fas fa-tools brand-image img-circle elevation-3" style="opacity: .8; margin-right: 0.5rem;"></i>
                <span class="brand-text font-weight-light" style="flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Manutenção Industrial</span>
                <a class="sidebar-toggle-btn" data-widget="pushmenu" href="#" role="button" style="margin-left: auto; color: inherit; background: transparent; border: none; padding: 0 12px; font-size: 1.3rem; display: flex; align-items: center; height: 100%;"><i class="fas fa-bars"></i></a>
            </div>
            
        <!-- Overlay para mobile -->
        <div class="sidebar-overlay"></div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link" title="Dashboard">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        
                        <?php if (isAdmin()): ?>
                        <!-- Menu Admin -->
                        <li class="nav-header">ADMINISTRAÇÃO</li>
                        <li class="nav-item">
                            <a href="listarUsuarios.php" class="nav-link" title="Usuários">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Usuários</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="listarEquipamentos.php" class="nav-link" title="Equipamentos">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Equipamentos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="listarMateriais.php" class="nav-link" title="Materiais">
                                <i class="nav-icon fas fa-boxes"></i>
                                <p>Materiais</p>
                            </a>
                        </li>
                        <?php else: ?>
                        <!-- Menu Técnico -->
                        <li class="nav-header">MANUTENÇÃO</li>
                        <li class="nav-item">
                            <a href="visualizarEquipamentos.php" class="nav-link" title="Equipamentos">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Equipamentos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="visualizarMateriais.php" class="nav-link" title="Materiais">
                                <i class="nav-icon fas fa-boxes"></i>
                                <p>Materiais</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <!-- Menu Comum -->
                        <li class="nav-header">ATIVIDADES</li>
                        <li class="nav-item">
                            <a href="formAtividade.php" class="nav-link" title="Nova Atividade">
                                <i class="nav-icon fas fa-plus"></i>
                                <p>Nova</p>
                            </a>
                        </li>
                        
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a href="listarAtividades.php" class="nav-link" title="Atividades">
                                <i class="nav-icon fas fa-list"></i>
                                <p>Atividades</p>
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a href="listarAtividades.php" class="nav-link" title="Atividades">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>Atividades</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (isAdmin()): ?>
                        <li class="nav-header">RELATÓRIOS</li>
                        <li class="nav-item">
                            <a href="relatorios.php" class="nav-link" title="Relatórios">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Relatórios</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">
                                <?php 
                                $pagina = basename($_SERVER['PHP_SELF'], '.php');
                                switch($pagina) {
                                    case 'dashboard': echo 'Dashboard'; break;
                                    case 'formUsuario': echo isAdmin() ? 'Gerenciar Usuários' : 'Usuários'; break;
                                    case 'formEquipamento': echo isAdmin() ? 'Gerenciar Equipamentos' : 'Equipamentos'; break;
                                    case 'formMaterial': echo isAdmin() ? 'Gerenciar Materiais' : 'Materiais'; break;
                                    case 'formAtividade': echo 'Nova Atividade'; break;
                                    case 'listarAtividades': echo 'Atividades'; break;
                                    case 'relatorios': echo 'Relatórios'; break;
                                    case 'perfil': echo 'Meu Perfil'; break;
                                    default: echo 'Sistema de Manutenção'; break;
                                }
                                ?>
                            </h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active">
                                    <?php 
                                    switch($pagina) {
                                        case 'dashboard': echo 'Dashboard'; break;
                                        case 'formUsuario': echo 'Usuários'; break;
                                        case 'formEquipamento': echo 'Equipamentos'; break;
                                        case 'formMaterial': echo 'Materiais'; break;
                                        case 'formAtividade': echo 'Nova Atividade'; break;
                                        case 'listarAtividades': echo 'Atividades'; break;
                                        case 'relatorios': echo 'Relatórios'; break;
                                        case 'perfil': echo 'Perfil'; break;
                                        default: echo 'Página'; break;
                                    }
                                    ?>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid"> 