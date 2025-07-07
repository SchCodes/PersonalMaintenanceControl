<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

// Verificar se é admin
if (!$is_admin) {
    header('Location: dashboard.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: listarUsuarios.php');
    exit;
}

$id = (int)$_GET['id'];

// Buscar dados do usuário
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = executarQuery($sql, [$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: listarUsuarios.php?msg=usuario_nao_encontrado');
    exit;
}
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Detalhes do Usuário
                </h3>
                <div class="card-tools">
                    <a href="listarUsuarios.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <a href="formUsuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">ID:</th>
                                <td><?php echo $usuario['id']; ?></td>
                            </tr>
                            <tr>
                                <th>Nome:</th>
                                <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                            </tr>
                            <tr>
                                <th>Login:</th>
                                <td><?php echo htmlspecialchars($usuario['login']); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($usuario['email'] ?? 'Não informado'); ?></td>
                            </tr>
                            <tr>
                                <th>Cargo:</th>
                                <td><?php echo htmlspecialchars($usuario['cargo'] ?? 'Não informado'); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Nível de Acesso:</th>
                                <td>
                                    <span class="badge badge-<?php echo $usuario['nivel_acesso'] === 'admin' ? 'danger' : 'info'; ?>">
                                        <?php echo ucfirst($usuario['nivel_acesso']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge badge-<?php echo $usuario['status'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $usuario['status'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Data de Criação:</th>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($usuario['data_criacao'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 