<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

// Verificar se é técnico
if ($is_admin) {
    header('Location: listarMateriais.php');
    exit;
}

require_once 'header.php';

// Buscar materiais
$sql = "SELECT * FROM materials ORDER BY nome";
$materiais = executarQuery($sql)->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes"></i> Materiais Disponíveis
                </h3>
            </div>
            
            <div class="card-body">
                <?php if (empty($materiais)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nenhum material cadastrado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tabelaMateriais">
                            <thead>
                                <tr>
                                    <th>Imagem</th>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Unidade de Medida</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($materiais as $mat): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($mat['imagem'])): ?>
                                            <?php if (file_exists($mat['imagem'])): ?>
                                                <img src="<?php echo $mat['imagem']; ?>" 
                                                     class="img-thumbnail" style="max-width: 50px; height: auto;" 
                                                     alt="Imagem do material" title="<?php echo htmlspecialchars($mat['nome']); ?>">
                                            <?php else: ?>
                                                <span class="text-muted">Imagem não encontrada</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <svg width="50" height="50" viewBox="0 0 24 24" fill="currentColor" class="text-muted">
                                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                            </svg>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($mat['codigo'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($mat['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($mat['descricao'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($mat['unidade_medida'] ?? ''); ?></td>
                                    <td>
                                        <a href="visualizarMateriais.php?id=<?php echo $mat['id']; ?>" 
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
    $('#tabelaMateriais').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.0/i18n/pt-BR.json"
        },
        "pageLength": 25,
        "order": [[1, "asc"]]
    });
});
</script>

<?php require_once 'footer.php'; ?> 