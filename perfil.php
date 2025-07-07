<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

$mensagem = '';

// Buscar dados do usuário
$sql = "SELECT * FROM users WHERE id = ?";
$usuario = executarQuery($sql, [$usuario_id])->fetch();

// Processar formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizar($_POST['nome']);
    $email = sanitizar($_POST['email']);
    $cargo = sanitizar($_POST['cargo']);
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $foto_caminho = $usuario['foto'];
    $remover_foto = isset($_POST['remover_foto']) ? true : false;

    // Upload de nova foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $validacao = validarImagem($_FILES['foto']);
        if ($validacao === true) {
            // Excluir foto antiga se existir
            if (!empty($usuario['foto']) && file_exists($usuario['foto'])) {
                excluirImagem($usuario['foto']);
            }
            $foto_caminho = salvarImagem($_FILES['foto'], 'img/usuarios');
            if (!$foto_caminho) {
                $mensagem = '<div class="alert alert-danger">Erro ao salvar a foto!</div>';
            }
        } else {
            $mensagem = '<div class="alert alert-danger">Erro na foto: ' . $validacao . '</div>';
        }
    }
    if ($remover_foto) {
        // Excluir foto antiga se existir
        if (!empty($usuario['foto']) && file_exists($usuario['foto'])) {
            excluirImagem($usuario['foto']);
        }
        $foto_caminho = null;
    }

    // Validar senha atual se for alterar
    if (!empty($nova_senha)) {
        if (!password_verify($senha_atual, $usuario['senha'])) {
            $mensagem = '<div class="alert alert-danger">Senha atual incorreta!</div>';
        } elseif ($nova_senha !== $confirmar_senha) {
            $mensagem = '<div class="alert alert-danger">As senhas não coincidem!</div>';
        } elseif (strlen($nova_senha) < 6) {
            $mensagem = '<div class="alert alert-danger">A nova senha deve ter pelo menos 6 caracteres!</div>';
        } else {
            // Atualizar com nova senha
            $hash_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
            $sql_update = "UPDATE users SET nome = ?, email = ?, cargo = ?, senha = ?, foto = ? WHERE id = ?";
            $stmt = executarQuery($sql_update, [$nome, $email, $cargo, $hash_senha, $foto_caminho, $usuario_id]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['nome'] = $nome;
                $mensagem = '<div class="alert alert-success">Perfil atualizado com sucesso!</div>';
                $usuario = executarQuery($sql, [$usuario_id])->fetch();
            } else {
                $mensagem = '<div class="alert alert-danger">Erro ao atualizar perfil!</div>';
            }
        }
    } else {
        // Atualizar sem alterar senha
        $sql_update = "UPDATE users SET nome = ?, email = ?, cargo = ?, foto = ? WHERE id = ?";
        $stmt = executarQuery($sql_update, [$nome, $email, $cargo, $foto_caminho, $usuario_id]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['nome'] = $nome;
            $mensagem = '<div class="alert alert-success">Perfil atualizado com sucesso!</div>';
            $usuario = executarQuery($sql, [$usuario_id])->fetch();
        } else {
            $mensagem = '<div class="alert alert-danger">Erro ao atualizar perfil!</div>';
        }
    }
}

// Buscar estatísticas do usuário
$sql_atividades = "SELECT COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ?";
$total_atividades = executarQuery($sql_atividades, [$usuario_id])->fetch()['total'];

$sql_concluidas = "SELECT COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ? AND status = 'concluida'";
$atividades_concluidas = executarQuery($sql_concluidas, [$usuario_id])->fetch()['total'];

$sql_mes = "SELECT COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ? AND MONTH(data_inicio) = MONTH(CURRENT_DATE()) AND YEAR(data_inicio) = YEAR(CURRENT_DATE())";
$atividades_mes = executarQuery($sql_mes, [$usuario_id])->fetch()['total'];
?>

<div class="row">
    <div class="col-md-4">
        <!-- Card do Perfil -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <?php if (!empty($usuario['foto'])): ?>
                        <img class="profile-user-img img-fluid img-circle" 
                             src="<?php echo $usuario['foto']; ?>" 
                             alt="Foto do usuário">
                    <?php else: ?>
                        <img class="profile-user-img img-fluid img-circle" 
                             src="https://via.placeholder.com/128x128/007bff/ffffff?text=<?php echo substr($usuario['nome'], 0, 1); ?>" 
                             alt="Foto do usuário">
                    <?php endif; ?>
                </div>
                
                <h3 class="profile-username text-center"><?php echo $usuario['nome']; ?></h3>
                <p class="text-muted text-center"><?php echo $usuario['cargo']; ?></p>
                
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Total de Atividades</b> <a class="float-right"><?php echo $total_atividades; ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Concluídas</b> <a class="float-right"><?php echo $atividades_concluidas; ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Este Mês</b> <a class="float-right"><?php echo $atividades_mes; ?></a>
                    </li>
                </ul>
                
                <div class="text-center">
                    <span class="badge badge-<?php echo $usuario['nivel_acesso'] === 'admin' ? 'danger' : 'info'; ?>">
                        <?php echo ucfirst($usuario['nivel_acesso']); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Formulário de Edição -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-edit"></i> Editar Perfil
                </h3>
            </div>
            
            <div class="card-body">
                <?php echo $mensagem; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome">Nome Completo</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo $usuario['nome']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="login">Login</label>
                                <input type="text" class="form-control" id="login" 
                                       value="<?php echo $usuario['login']; ?>" readonly>
                                <small class="form-text text-muted">O login não pode ser alterado.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $usuario['email']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cargo">Cargo</label>
                                <input type="text" class="form-control" id="cargo" name="cargo" 
                                       value="<?php echo $usuario['cargo']; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nivel">Nível de Acesso</label>
                                <input type="text" class="form-control" id="nivel" 
                                       value="<?php echo ucfirst($usuario['nivel_acesso']); ?>" readonly>
                                <small class="form-text text-muted">O nível de acesso não pode ser alterado.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="foto">Foto do Usuário</label>
                                <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png">
                                <?php if (!empty($usuario['foto'])): ?>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="remover_foto" id="remover_foto">
                                        <label class="form-check-label" for="remover_foto">Remover foto atual</label>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    <h5>Alterar Senha</h5>
                    <small class="text-muted">Deixe em branco se não quiser alterar a senha.</small>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="senha_atual">Senha Atual</label>
                                <input type="password" class="form-control" id="senha_atual" name="senha_atual">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nova_senha">Nova Senha</label>
                                <input type="password" class="form-control" id="nova_senha" name="nova_senha" 
                                       minlength="6">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="confirmar_senha">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" 
                                       minlength="6">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 