<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

// Verificar se é admin
if (!$is_admin) {
    header('Location: dashboard.php');
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
                    <i class="fas fa-boxes"></i> Gerenciar Materiais
                </h3>
                <div class="card-tools">
                    <a href="formMaterial.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Material
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php 
                        switch($_GET['msg']) {
                            case 'material_excluido': echo 'Material excluído com sucesso!'; break;
                            case 'material_atualizado': echo 'Material atualizado com sucesso!'; break;
                            case 'material_cadastrado': echo 'Material cadastrado com sucesso!'; break;
                            case 'material_nao_encontrado': echo 'Material não encontrado!'; break;
                            case 'material_com_uso': echo 'Não é possível excluir material que está sendo usado em atividades!'; break;
                            case 'erro_excluir': echo 'Erro ao excluir material!'; break;
                            case 'erro_banco': echo 'Erro no banco de dados!'; break;
                            default: echo $_GET['msg']; break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
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
                                        <div class="btn-group">
                                            <a href="visualizarMateriais.php?id=<?php echo $mat['id']; ?>" 
                                               class="btn btn-sm btn-info" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="formMaterial.php?id=<?php echo $mat['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="excluirMaterial.php?id=<?php echo $mat['id']; ?>" 
                                               class="btn btn-sm btn-danger" title="Excluir"
                                               onclick="return confirm('Tem certeza que deseja excluir este material? Esta ação não pode ser desfeita.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
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