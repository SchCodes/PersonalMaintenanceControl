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

// Buscar equipamentos
$sql = "SELECT * FROM equipment ORDER BY nome";
$equipamentos = executarQuery($sql)->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs"></i> Gerenciar Equipamentos
                </h3>
                <div class="card-tools">
                    <a href="formEquipamento.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Equipamento
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php 
                        switch($_GET['msg']) {
                            case 'equipamento_ativado': echo 'Equipamento ativado com sucesso!'; break;
                            case 'equipamento_desativado': echo 'Equipamento desativado com sucesso!'; break;
                            case 'equipamento_excluido': echo 'Equipamento excluído com sucesso!'; break;
                            case 'equipamento_atualizado': echo 'Equipamento atualizado com sucesso!'; break;
                            case 'equipamento_cadastrado': echo 'Equipamento cadastrado com sucesso!'; break;
                            case 'equipamento_nao_encontrado': echo 'Equipamento não encontrado!'; break;
                            case 'equipamento_com_atividades': echo 'Não é possível excluir equipamento que possui atividades vinculadas!'; break;
                            case 'erro_excluir': echo 'Erro ao excluir equipamento!'; break;
                            case 'erro_alterar_status': echo 'Erro ao alterar status do equipamento!'; break;
                            case 'erro_banco': echo 'Erro no banco de dados!'; break;
                            default: echo $_GET['msg']; break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
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
                                        <div class="btn-group">
                                            <a href="visualizarEquipamentos.php?id=<?php echo $equip['id']; ?>" 
                                               class="btn btn-sm btn-info" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="formEquipamento.php?id=<?php echo $equip['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="alterarStatusEquipamento.php?id=<?php echo $equip['id']; ?>&status=<?php echo $equip['status'] === 'ativo' ? 'inativo' : 'ativo'; ?>" 
                                               class="btn btn-sm btn-<?php echo $equip['status'] === 'ativo' ? 'secondary' : 'success'; ?>" 
                                               title="<?php echo $equip['status'] === 'ativo' ? 'Desativar' : 'Ativar'; ?>"
                                               onclick="return confirm('Tem certeza que deseja <?php echo $equip['status'] === 'ativo' ? 'desativar' : 'ativar'; ?> este equipamento?')">
                                                <i class="fas fa-<?php echo $equip['status'] === 'ativo' ? 'ban' : 'check'; ?>"></i>
                                            </a>
                                            <a href="excluirEquipamento.php?id=<?php echo $equip['id']; ?>" 
                                               class="btn btn-sm btn-danger" title="Excluir"
                                               onclick="return confirm('Tem certeza que deseja excluir este equipamento? Esta ação não pode ser desfeita.')">
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