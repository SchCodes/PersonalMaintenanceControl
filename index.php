<?php
session_start();
require_once 'conexaoBD.php';

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Verificar se o verificar_banco.php já foi executado
$ajuste_feito = false;
try {
    $sql_config = "SELECT ajuste_inicial FROM configuracao WHERE id = 1";
    $stmt_config = executarQuery($sql_config);
    $row_config = $stmt_config->fetch();
    if ($row_config) {
        $ajuste_feito = (bool)$row_config['ajuste_inicial'];
    }
} catch (Exception $e) {
    // Se der erro (tabela não existe), considera que ainda não foi executado
    $ajuste_feito = false;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Manutenção Industrial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: #343a40;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-body {
            padding: 40px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-tools fa-3x mb-3"></i>
            <h3>Sistema de Manutenção</h3>
            <p class="mb-0">Controle Industrial</p>
        </div>
        <div class="login-body">
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    switch($_GET['msg']) {
                        case 'erro_login': echo 'Usuário ou senha incorretos!'; break;
                        case 'sessao_expirada': echo 'Sessão expirada. Faça login novamente.'; break;
                        case 'sem_permissao': echo 'Você não tem permissão para acessar esta área.'; break;
                        default: echo 'Erro desconhecido.'; break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Observação para primeira vez -->
            <?php if (!$ajuste_feito): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Primeira vez usando o sistema?</strong><br>
                Se der erro no primeiro login, tente novamente! O sistema executa uma verificação automática na primeira tentativa.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form action="actionLogin.php" method="POST">
                <div class="mb-3">
                    <label for="login" class="form-label">Usuário</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="login" name="login" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="senha" class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Entrar
                </button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <strong>Usuários de Teste:</strong><br>
                    Admin: admin / admin123<br>
                    Técnico: tecnico / tecnico123
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 