<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

// Buscar materiais
$sql = "SELECT * FROM materials ORDER BY nome";
$materiais = executarQuery($sql)->fetchAll();

// Estatísticas
$total_materiais = count($materiais);
$materiais_com_codigo = count(array_filter($materiais, function($mat) { return !empty($mat['codigo']); }));
$materiais_com_descricao = count(array_filter($materiais, function($mat) { return !empty($mat['descricao']); }));
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes"></i> Relatório de Materiais
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
                                <h3><?php echo $total_materiais; ?></h3>
                                <p>Total de Materiais</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $materiais_com_codigo; ?></h3>
                                <p>Com Código</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-barcode"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $materiais_com_descricao; ?></h3>
                                <p>Com Descrição</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $total_materiais - $materiais_com_codigo; ?></h3>
                                <p>Sem Código</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Materiais -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-list"></i> Lista de Materiais
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="tabelaRelatorio">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="10%">Código</th>
                                        <th width="25%">Nome</th>
                                        <th width="35%">Descrição</th>
                                        <th width="15%">Unidade</th>
                                        <th width="15%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materiais as $mat): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($mat['codigo'])): ?>
                                                <span class="badge badge-primary"><?php echo htmlspecialchars($mat['codigo']); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($mat['nome']); ?></strong>
                                        </td>
                                        <td>
                                            <?php if (!empty($mat['descricao'])): ?>
                                                <?php echo htmlspecialchars($mat['descricao']); ?>
                                            <?php else: ?>
                                                <em class="text-muted">Sem descrição</em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($mat['unidade_medida'])): ?>
                                                <span class="badge badge-info"><?php echo htmlspecialchars($mat['unidade_medida']); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-light">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Disponível
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
        "order": [[1, "asc"]],
        "responsive": true,
        "dom": '<"top"lf>rt<"bottom"ip><"clear">',
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
    });
});
</script>

<?php require_once 'footer.php'; ?> 