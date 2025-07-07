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

// Buscar usuários
$sql = "SELECT * FROM users ORDER BY nome";
$usuarios = executarQuery($sql)->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Gerenciar Usuários
                </h3>
                <div class="card-tools">
                    <a href="formUsuario.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Usuário
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php 
                        switch($_GET['msg']) {
                            case 'usuario_ativado': echo 'Usuário ativado com sucesso!'; break;
                            case 'usuario_desativado': echo 'Usuário desativado com sucesso!'; break;
                            case 'usuario_excluido': echo 'Usuário excluído com sucesso!'; break;
                            case 'usuario_excluido_com_atividades': 
                                $atividades = isset($_GET['atividades']) ? $_GET['atividades'] : 0;
                                echo "Usuário excluído com sucesso! ({$atividades} atividades também foram removidas)"; 
                                break;
                            case 'usuario_atualizado': echo 'Usuário atualizado com sucesso!'; break;
                            case 'usuario_cadastrado': echo 'Usuário cadastrado com sucesso!'; break;
                            case 'nao_pode_desativar_proprio_usuario': echo 'Você não pode desativar seu próprio usuário!'; break;
                            case 'nao_pode_excluir_proprio_usuario': echo 'Você não pode excluir seu próprio usuário!'; break;
                            case 'usuario_com_atividades': echo 'Não é possível excluir usuário com atividades vinculadas!'; break;
                            case 'atividades_excluidas_sucesso': 
                                $total = isset($_GET['total']) ? $_GET['total'] : 0;
                                echo "Atividades excluídas com sucesso! ({$total} atividades removidas)"; 
                                break;
                            case 'atividades_excluidas_com_erros': 
                                $total = isset($_GET['total']) ? $_GET['total'] : 0;
                                $erros = isset($_GET['erros']) ? $_GET['erros'] : 0;
                                echo "Atividades excluídas com alguns erros! ({$total} excluídas, {$erros} erros)"; 
                                break;
                            case 'usuario_nao_encontrado': echo 'Usuário não encontrado!'; break;
                            case 'id_nao_fornecido': echo 'ID do usuário não fornecido!'; break;
                            case 'id_usuario_nao_fornecido': echo 'ID do usuário não fornecido!'; break;
                            case 'erro_excluir_usuario': echo 'Erro ao excluir usuário!'; break;
                            case 'erro_alterar_status': echo 'Erro ao alterar status do usuário!'; break;
                            case 'erro_banco': echo 'Erro no banco de dados!'; break;
                            default: echo $_GET['msg']; break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($usuarios)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nenhum usuário cadastrado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tabelaUsuarios">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Login</th>
                                    <th>Cargo</th>
                                    <th>Email</th>
                                    <th>Nível</th>
                                    <th>Status</th>
                                    <th>Data Criação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($user['login']); ?></td>
                                    <td><?php echo htmlspecialchars($user['cargo'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['nivel_acesso'] === 'admin' ? 'danger' : 'info'; ?>">
                                            <?php echo ucfirst($user['nivel_acesso']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['status'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $user['status'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($user['data_criacao'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="visualizarUsuario.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-info" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="formUsuario.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] != $usuario_id): ?>
                                            <a href="alterarStatusUsuario.php?id=<?php echo $user['id']; ?>&status=<?php echo $user['status'] ? '0' : '1'; ?>" 
                                               class="btn btn-sm btn-<?php echo $user['status'] ? 'secondary' : 'success'; ?>" 
                                               title="<?php echo $user['status'] ? 'Desativar' : 'Ativar'; ?>"
                                               onclick="return confirm('Tem certeza que deseja <?php echo $user['status'] ? 'desativar' : 'ativar'; ?> este usuário?')">
                                                <i class="fas fa-<?php echo $user['status'] ? 'ban' : 'check'; ?>"></i>
                                            </a>
                                            <a href="excluirUsuarioCompleto.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-danger" title="Excluir Usuário"
                                               onclick="return confirm('Tem certeza que deseja excluir este usuário? Se houver atividades vinculadas, elas também serão removidas. Esta ação não pode ser desfeita.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="excluirAtividadesUsuario.php?usuario_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Excluir Atividades"
                                               onclick="return confirm('Tem certeza que deseja excluir TODAS as atividades deste usuário? Esta ação não pode ser desfeita.')">
                                                <i class="fas fa-clipboard-list"></i>
                                            </a>
                                            <?php endif; ?>
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
    $('#tabelaUsuarios').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.0/i18n/pt-BR.json"
        },
        "pageLength": 25,
        "order": [[1, "asc"]]
    });
});
</script>

<?php require_once 'footer.php'; ?> 