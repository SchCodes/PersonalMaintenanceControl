<?php
session_start();
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'] ?? 0;
$is_admin = $_SESSION['nivel_acesso'] === 'admin';

// Verificar se é admin
if (!$is_admin) {
    header('Location: dashboard.php');
    exit;
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = (int)$_GET['status'];
    
    // Não permitir desativar o próprio usuário
    if ($id == $usuario_id) {
        header('Location: listarUsuarios.php?msg=nao_pode_desativar_proprio_usuario');
        exit;
    }
    
    try {
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = executarQuery($sql, [$status, $id]);
        
        if ($stmt->rowCount() > 0) {
            $acao = $status ? 'ativado' : 'desativado';
            header('Location: listarUsuarios.php?msg=usuario_' . $acao);
        } else {
            header('Location: listarUsuarios.php?msg=erro_alterar_status');
        }
    } catch (Exception $e) {
        header('Location: listarUsuarios.php?msg=erro_banco');
    }
} else {
    header('Location: listarUsuarios.php');
}

exit;
?> 