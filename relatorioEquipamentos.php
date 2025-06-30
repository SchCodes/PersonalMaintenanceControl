<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

// Buscar equipamentos
$sql = "SELECT * FROM equipment ORDER BY nome";
$equipamentos = executarQuery($sql)->fetchAll();

// Estatísticas
$total_equipamentos = count($equipamentos);
$equipamentos_ativos = count(array_filter($equipamentos, function($equip) { return $equip['status'] === 'ativo'; }));
$equipamentos_inativos = count(array_filter($equipamentos, function($equip) { return $equip['status'] === 'inativo'; }));
$equipamentos_manutencao = count(array_filter($equipamentos, function($equip) { return $equip['status'] === 'manutencao'; }));

// Contar por tipo
$tipos = [];
foreach ($equipamentos as $equip) {
    $tipo = $equip['tipo'] ?? 'Não definido';
    $tipos[$tipo] = ($tipos[$tipo] ?? 0) + 1;
}
arsort($tipos);
$tipos_principais = array_slice($tipos, 0, 3, true);
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs"></i> Relatório de Equipamentos
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
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3><?php echo $total_equipamentos; ?></h3>
                                <p>Total de Equipamentos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $equipamentos_ativos; ?></h3>
                                <p>Ativos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $equipamentos_manutencao; ?></h3>
                                <p>Em Manutenção</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-tools"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo $equipamentos_inativos; ?></h3>
                                <p>Inativos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tipos Principais -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-pie"></i> Principais Tipos de Equipamentos
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($tipos_principais as $tipo => $quantidade): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info">
                                                <i class="fas fa-cog"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text"><?php echo $tipo; ?></span>
                                                <span class="info-box-number"><?php echo $quantidade; ?></span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-info" 
                                                         style="width: <?php echo ($quantidade / $total_equipamentos) * 100; ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Equipamentos -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-list"></i> Lista de Equipamentos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="tabelaRelatorio">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="12%">Código</th>
                                        <th width="25%">Nome</th>
                                        <th width="15%">Tipo</th>
                                        <th width="12%">Status</th>
                                        <th width="18%">Localização</th>
                                        <th width="18%">Área</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipamentos as $equip): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($equip['codigo'])): ?>
                                                <span class="badge badge-primary"><?php echo htmlspecialchars($equip['codigo']); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($equip['nome']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($equip['tipo'] ?? 'Não definido'); ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_class = $equip['status'] === 'ativo' ? 'success' : 
                                                ($equip['status'] === 'inativo' ? 'danger' : 'warning');
                                            $status_icon = $equip['status'] === 'ativo' ? 'check' : 
                                                ($equip['status'] === 'inativo' ? 'times' : 'tools');
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <i class="fas fa-<?php echo $status_icon; ?>"></i> 
                                                <?php echo ucfirst($equip['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($equip['localizacao'])): ?>
                                                <i class="fas fa-map-marker-alt text-muted"></i> 
                                                <?php echo htmlspecialchars($equip['localizacao']); ?>
                                            <?php else: ?>
                                                <em class="text-muted">Não informado</em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($equip['area_planta'])): ?>
                                                <i class="fas fa-industry text-muted"></i> 
                                                <?php echo htmlspecialchars($equip['area_planta']); ?>
                                            <?php else: ?>
                                                <em class="text-muted">Não informado</em>
                                            <?php endif; ?>
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
        "order": [[1, "asc"]],
        "responsive": true,
        "dom": '<"top"lf>rt<"bottom"ip><"clear">',
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
    });
});
</script>

<?php require_once 'footer.php'; ?> 