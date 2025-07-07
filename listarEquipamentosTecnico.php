<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

// Verificar se é técnico
if ($is_admin) {
    header('Location: listarEquipamentos.php');
    exit;
}

require_once 'header.php';

// Buscar equipamentos
$sql = "SELECT * FROM equipment ORDER BY nome";
$equipamentos = executarQuery($sql)->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs"></i> Equipamentos Disponíveis
                </h3>
            </div>
            
            <div class="card-body">
                <?php if (empty($equipamentos)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nenhum equipamento cadastrado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tabelaEquipamentos">
                            <thead>
                                <tr>
                                    <th>Imagem</th>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Localização</th>
                                    <th>Área</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipamentos as $equip): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($equip['imagem'])): ?>
                                            <?php if (file_exists($equip['imagem'])): ?>
                                                <img src="<?php echo $equip['imagem']; ?>" 
                                                     class="img-thumbnail" style="max-width: 50px; height: auto;" 
                                                     alt="Imagem do equipamento" title="<?php echo htmlspecialchars($equip['nome']); ?>">
                                            <?php else: ?>
                                                <span class="text-muted">Imagem não encontrada</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <svg width="50" height="50" viewBox="0 0 24 24" fill="currentColor" class="text-muted">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                            </svg>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($equip['codigo'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($equip['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($equip['tipo'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($equip['localizacao'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($equip['area_planta'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $equip['status'] === 'ativo' ? 'success' : 
                                                ($equip['status'] === 'inativo' ? 'secondary' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($equip['status'] ?? 'ativo'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="visualizarEquipamentos.php?id=<?php echo $equip['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Visualizar">
                                            <i class="fas fa-eye"></i> Visualizar
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

<script>
$(document).ready(function() {
    $('#tabelaEquipamentos').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.0/i18n/pt-BR.json"
        },
        "pageLength": 25,
        "order": [[1, "asc"]]
    });
});
</script>

<?php require_once 'footer.php'; ?> 