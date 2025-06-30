<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

// Estatísticas para o dashboard
$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

// Total de atividades
$sql_atividades = $is_admin ? 
    "SELECT COUNT(*) as total FROM maintenance_activities" :
    "SELECT COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ?";
$params_atividades = $is_admin ? [] : [$usuario_id];
$total_atividades = executarQuery($sql_atividades, $params_atividades)->fetch()['total'];

// Atividades do mês
$sql_mes = $is_admin ?
    "SELECT COUNT(*) as total FROM maintenance_activities WHERE MONTH(data_inicio) = MONTH(CURRENT_DATE()) AND YEAR(data_inicio) = YEAR(CURRENT_DATE())" :
    "SELECT COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ? AND MONTH(data_inicio) = MONTH(CURRENT_DATE()) AND YEAR(data_inicio) = YEAR(CURRENT_DATE())";
$params_mes = $is_admin ? [] : [$usuario_id];
$atividades_mes = executarQuery($sql_mes, $params_mes)->fetch()['total'];

// Total de equipamentos
$total_equipamentos = executarQuery("SELECT COUNT(*) as total FROM equipment")->fetch()['total'];

// Total de materiais
$total_materiais = executarQuery("SELECT COUNT(*) as total FROM materials")->fetch()['total'];

// Atividades recentes
$sql_recentes = $is_admin ?
    "SELECT ma.*, u.nome as tecnico, e.nome as equipamento 
     FROM maintenance_activities ma 
     JOIN users u ON ma.usuario_id = u.id 
     JOIN equipment e ON ma.equipamento_id = e.id 
     ORDER BY ma.data_registro DESC LIMIT 5" :
    "SELECT ma.*, e.nome as equipamento 
     FROM maintenance_activities ma 
     JOIN equipment e ON ma.equipamento_id = e.id 
     WHERE ma.usuario_id = ? 
     ORDER BY ma.data_registro DESC LIMIT 5";
$params_recentes = $is_admin ? [] : [$usuario_id];
$atividades_recentes = executarQuery($sql_recentes, $params_recentes)->fetchAll();
?>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?php echo $total_atividades; ?></h3>
                <p><?php echo $is_admin ? 'Total de Atividades' : 'Minhas Atividades'; ?></p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <a href="listarAtividades.php" class="small-box-footer">
                Ver todas <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?php echo $atividades_mes; ?></h3>
                <p><?php echo $is_admin ? 'Atividades do Mês' : 'Este Mês'; ?></p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <a href="listarAtividades.php" class="small-box-footer">
                Ver detalhes <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?php echo $total_equipamentos; ?></h3>
                <p>Equipamentos</p>
            </div>
            <div class="icon">
                <i class="fas fa-cogs"></i>
            </div>
            <a href="formEquipamento.php" class="small-box-footer">
                <?php echo $is_admin ? 'Gerenciar' : 'Visualizar'; ?> <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?php echo $total_materiais; ?></h3>
                <p>Materiais</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
            <a href="formMaterial.php" class="small-box-footer">
                <?php echo $is_admin ? 'Gerenciar' : 'Visualizar'; ?> <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i> <?php echo $is_admin ? 'Atividades Recentes da Equipe' : 'Minhas Atividades Recentes'; ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($atividades_recentes)): ?>
                    <p class="text-muted">Nenhuma atividade encontrada.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <?php if ($is_admin): ?>
                                    <th>Técnico</th>
                                    <?php endif; ?>
                                    <th>Equipamento</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($atividades_recentes as $atividade): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($atividade['data_inicio'])); ?></td>
                                    <?php if ($is_admin): ?>
                                    <td><?php echo $atividade['tecnico']; ?></td>
                                    <?php endif; ?>
                                    <td><?php echo $atividade['equipamento']; ?></td>
                                    <td><?php echo $atividade['titulo']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $atividade['tipo_manutencao'] === 'preventiva' ? 'success' : 
                                                ($atividade['tipo_manutencao'] === 'corretiva' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($atividade['tipo_manutencao']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $atividade['status'] === 'concluida' ? 'success' : 
                                                ($atividade['status'] === 'pendente' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo ucfirst($atividade['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="visualizarAtividade.php?id=<?php echo $atividade['id']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 