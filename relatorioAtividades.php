<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

// Buscar atividades
$sql = $is_admin ? 
    "SELECT ma.*, u.nome as tecnico, e.nome as equipamento 
     FROM maintenance_activities ma 
     JOIN users u ON ma.usuario_id = u.id 
     JOIN equipment e ON ma.equipamento_id = e.id 
     ORDER BY ma.data_inicio DESC" :
    "SELECT ma.*, e.nome as equipamento 
     FROM maintenance_activities ma 
     JOIN equipment e ON ma.equipamento_id = e.id 
     WHERE ma.usuario_id = ? 
     ORDER BY ma.data_inicio DESC";

$params = $is_admin ? [] : [$usuario_id];
$atividades = executarQuery($sql, $params)->fetchAll();

// Estatísticas
$total_atividades = count($atividades);
$atividades_concluidas = count(array_filter($atividades, function($atv) { return $atv['status'] === 'concluida'; }));
$atividades_pendentes = count(array_filter($atividades, function($atv) { return $atv['status'] === 'pendente'; }));
$atividades_andamento = count(array_filter($atividades, function($atv) { return $atv['status'] === 'em_andamento'; }));

// Por tipo
$preventivas = count(array_filter($atividades, function($atv) { return $atv['tipo_manutencao'] === 'preventiva'; }));
$corretivas = count(array_filter($atividades, function($atv) { return $atv['tipo_manutencao'] === 'corretiva'; }));
$preditivas = count(array_filter($atividades, function($atv) { return $atv['tipo_manutencao'] === 'preditiva'; }));

// Por prioridade
$alta_prioridade = count(array_filter($atividades, function($atv) { return $atv['prioridade'] === 'alta'; }));
$media_prioridade = count(array_filter($atividades, function($atv) { return $atv['prioridade'] === 'media'; }));
$baixa_prioridade = count(array_filter($atividades, function($atv) { return $atv['prioridade'] === 'baixa'; }));

// Atividades do mês atual
$atividades_mes = count(array_filter($atividades, function($atv) { 
    return date('Y-m') === date('Y-m', strtotime($atv['data_inicio'])); 
}));
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list"></i> Relatório de Atividades
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
                                <h3><?php echo $total_atividades; ?></h3>
                                <p>Total</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $atividades_concluidas; ?></h3>
                                <p>Concluídas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $atividades_pendentes; ?></h3>
                                <p>Pendentes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $atividades_andamento; ?></h3>
                                <p>Em Andamento</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-tools"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo $alta_prioridade; ?></h3>
                                <p>Alta Prioridade</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3><?php echo $atividades_mes; ?></h3>
                                <p>Este Mês</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo por Tipo -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-pie"></i> Distribuição por Tipo de Manutenção
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success">
                                                <i class="fas fa-shield-alt"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Preventiva</span>
                                                <span class="info-box-number"><?php echo $preventivas; ?></span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" 
                                                         style="width: <?php echo $total_atividades > 0 ? ($preventivas / $total_atividades) * 100 : 0; ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-danger">
                                                <i class="fas fa-wrench"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Corretiva</span>
                                                <span class="info-box-number"><?php echo $corretivas; ?></span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-danger" 
                                                         style="width: <?php echo $total_atividades > 0 ? ($corretivas / $total_atividades) * 100 : 0; ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning">
                                                <i class="fas fa-chart-line"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Preditiva</span>
                                                <span class="info-box-number"><?php echo $preditivas; ?></span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-warning" 
                                                         style="width: <?php echo $total_atividades > 0 ? ($preditivas / $total_atividades) * 100 : 0; ?>%">
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

                <!-- Tabela de Atividades -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-list"></i> Lista de Atividades
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="tabelaRelatorio">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="12%">Data</th>
                                        <?php if ($is_admin): ?>
                                        <th width="15%">Técnico</th>
                                        <?php endif; ?>
                                        <th width="18%">Equipamento</th>
                                        <th width="25%">Título</th>
                                        <th width="10%">Tipo</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Prioridade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($atividades as $atividade): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-calendar text-muted"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($atividade['data_inicio'])); ?>
                                        </td>
                                        <?php if ($is_admin): ?>
                                        <td>
                                            <i class="fas fa-user text-muted"></i>
                                            <?php echo htmlspecialchars($atividade['tecnico']); ?>
                                        </td>
                                        <?php endif; ?>
                                        <td>
                                            <i class="fas fa-cog text-muted"></i>
                                            <?php echo htmlspecialchars($atividade['equipamento']); ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($atividade['titulo']); ?></strong>
                                        </td>
                                        <td>
                                            <?php 
                                            $tipo_class = $atividade['tipo_manutencao'] === 'preventiva' ? 'success' : 
                                                ($atividade['tipo_manutencao'] === 'corretiva' ? 'danger' : 'warning');
                                            ?>
                                            <span class="badge badge-<?php echo $tipo_class; ?>">
                                                <?php echo ucfirst($atividade['tipo_manutencao']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_class = $atividade['status'] === 'concluida' ? 'success' : 
                                                ($atividade['status'] === 'pendente' ? 'warning' : 'info');
                                            $status_icon = $atividade['status'] === 'concluida' ? 'check' : 
                                                ($atividade['status'] === 'pendente' ? 'clock' : 'tools');
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <i class="fas fa-<?php echo $status_icon; ?>"></i>
                                                <?php echo ucfirst($atividade['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $prioridade_class = $atividade['prioridade'] === 'alta' ? 'danger' : 
                                                ($atividade['prioridade'] === 'media' ? 'warning' : 'success');
                                            ?>
                                            <span class="badge badge-<?php echo $prioridade_class; ?>">
                                                <?php echo ucfirst($atividade['prioridade']); ?>
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
        "order": [[0, "desc"]],
        "responsive": true,
        "dom": '<"top"lf>rt<"bottom"ip><"clear">',
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
    });
});
</script>

<?php require_once 'footer.php'; ?> 