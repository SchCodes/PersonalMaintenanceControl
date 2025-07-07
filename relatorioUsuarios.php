<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

// Buscar usuários
$sql = "SELECT * FROM users ORDER BY nome";
$usuarios = executarQuery($sql)->fetchAll();

// Estatísticas
$total_usuarios = count($usuarios);
$usuarios_ativos = count(array_filter($usuarios, function($user) { return $user['status']; }));
$usuarios_inativos = count(array_filter($usuarios, function($user) { return !$user['status']; }));
$admins = count(array_filter($usuarios, function($user) { return $user['nivel_acesso'] === 'admin'; }));
$tecnicos = count(array_filter($usuarios, function($user) { return $user['nivel_acesso'] === 'tecnico'; }));
$usuarios_com_email = count(array_filter($usuarios, function($user) { return !empty($user['email']); }));

// Usuários criados este mês
$usuarios_mes = count(array_filter($usuarios, function($user) { 
    return date('Y-m') === date('Y-m', strtotime($user['data_criacao'])); 
}));
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Relatório de Usuários
                </h3>
                <div class="card-tools">
                    <a href="relatorios.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Cards de Resumo -->
                <div class="row mb-4">
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3><?php echo $total_usuarios; ?></h3>
                                <p>Total de Usuários</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $usuarios_ativos; ?></h3>
                                <p>Ativos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo $usuarios_inativos; ?></h3>
                                <p>Inativos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $admins; ?></h3>
                                <p>Administradores</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $tecnicos; ?></h3>
                                <p>Técnicos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3><?php echo $usuarios_mes; ?></h3>
                                <p>Este Mês</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo por Nível -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-pie"></i> Distribuição por Nível de Acesso
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning">
                                                <i class="fas fa-user-shield"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Administradores</span>
                                                <span class="info-box-number"><?php echo $admins; ?></span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-warning" 
                                                         style="width: <?php echo $total_usuarios > 0 ? ($admins / $total_usuarios) * 100 : 0; ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info">
                                                <i class="fas fa-user-cog"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Técnicos</span>
                                                <span class="info-box-number"><?php echo $tecnicos; ?></span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-info" 
                                                         style="width: <?php echo $total_usuarios > 0 ? ($tecnicos / $total_usuarios) * 100 : 0; ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Usuários -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-list"></i> Lista de Usuários
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="tabelaRelatorio">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="25%">Nome</th>
                                        <th width="20%">Login</th>
                                        <th width="25%">Email</th>
                                        <th width="15%">Nível</th>
                                        <th width="15%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $user): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-user text-muted"></i>
                                            <strong><?php echo htmlspecialchars($user['nome']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary"><?php echo htmlspecialchars($user['login']); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($user['email'])): ?>
                                                <i class="fas fa-envelope text-muted"></i>
                                                <?php echo htmlspecialchars($user['email']); ?>
                                            <?php else: ?>
                                                <em class="text-muted">Não informado</em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $nivel_class = $user['nivel_acesso'] === 'admin' ? 'warning' : 'info';
                                            $nivel_icon = $user['nivel_acesso'] === 'admin' ? 'user-shield' : 'user-cog';
                                            ?>
                                            <span class="badge badge-<?php echo $nivel_class; ?>">
                                                <i class="fas fa-<?php echo $nivel_icon; ?>"></i>
                                                <?php echo ucfirst($user['nivel_acesso']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_class = $user['status'] ? 'success' : 'danger';
                                            $status_icon = $user['status'] ? 'check' : 'times';
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <i class="fas fa-<?php echo $status_icon; ?>"></i>
                                                <?php echo $user['status'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tabelaRelatorio').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.0/i18n/pt-BR.json"
        },
        "pageLength": 25,
        "order": [[0, "asc"]],
        "responsive": true,
        "dom": '<"top"lf>rt<"bottom"ip><"clear">',
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
    });
});
</script>

<?php require_once 'footer.php'; ?> 