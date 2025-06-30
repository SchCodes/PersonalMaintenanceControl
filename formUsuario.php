<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';
verificarPermissao('admin');

$editar = false;
$usuario = null;

if (isset($_GET['id'])) {
    $editar = true;
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $usuario = executarQuery($sql, [$id])->fetch();
    
    if (!$usuario) {
        header("Location: listarUsuarios.php?msg=usuario_nao_encontrado");
        exit();
    }
}
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-edit"></i> 
                    <?php echo $editar ? 'Editar Usuário' : 'Novo Usuário'; ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        switch($_GET['msg']) {
                            case 'senha_obrigatoria': echo 'Senha é obrigatória!'; break;
                            case 'senha_minimo_6_caracteres': echo 'A senha deve ter pelo menos 6 caracteres!'; break;
                            default: echo htmlspecialchars($_GET['msg']); break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form action="actionUsuario.php" method="POST" id="formUsuario">
                    <?php if ($editar): ?>
                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome">Nome Completo *</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo $editar ? $usuario['nome'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="login">Login *</label>
                                <input type="text" class="form-control" id="login" name="login" 
                                       value="<?php echo $editar ? $usuario['login'] : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="senha"><?php echo $editar ? 'Nova Senha' : 'Senha'; ?> *</label>
                                <input type="password" class="form-control" id="senha" name="senha" 
                                       <?php echo $editar ? '' : 'required'; ?> minlength="6" 
                                       pattern=".{6,}" title="A senha deve ter pelo menos 6 caracteres">
                                <?php if ($editar): ?>
                                    <small class="form-text text-muted">Deixe em branco para manter a senha atual</small>
                                <?php else: ?>
                                    <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $editar ? $usuario['email'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cargo">Cargo</label>
                                <input type="text" class="form-control" id="cargo" name="cargo" 
                                       value="<?php echo $editar ? $usuario['cargo'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nivel_acesso">Nível de Acesso *</label>
                                <select class="form-control" id="nivel_acesso" name="nivel_acesso" required>
                                    <option value="">Selecione...</option>
                                    <option value="admin" <?php echo $editar && $usuario['nivel_acesso'] === 'admin' ? 'selected' : ''; ?>>
                                        Administrador
                                    </option>
                                    <option value="tecnico" <?php echo $editar && $usuario['nivel_acesso'] === 'tecnico' ? 'selected' : ''; ?>>
                                        Técnico
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="1" <?php echo $editar && $usuario['status'] ? 'selected' : ''; ?>>
                                        Ativo
                                    </option>
                                    <option value="0" <?php echo $editar && !$usuario['status'] ? 'selected' : ''; ?>>
                                        Inativo
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?php echo $editar ? 'Atualizar' : 'Cadastrar'; ?>
                            </button>
                            <a href="listarUsuarios.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Informações
                </h3>
            </div>
            <div class="card-body">
                <p><strong>Níveis de Acesso:</strong></p>
                <ul>
                    <li><strong>Administrador:</strong> Acesso completo ao sistema</li>
                    <li><strong>Técnico:</strong> Acesso limitado às funcionalidades de manutenção</li>
                </ul>
                
                <p><strong>Senha:</strong></p>
                <ul>
                    <li>Mínimo 6 caracteres</li>
                    <li>Será criptografada automaticamente</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 