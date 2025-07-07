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

if (isset($_GET['id'])) {
    $material_id = (int)$_GET['id'];
    
    try {
        // Verificar se o material existe
        $sql_check = "SELECT id, nome FROM materials WHERE id = ?";
        $stmt_check = executarQuery($sql_check, [$material_id]);
        $material = $stmt_check->fetch();
        
        if (!$material) {
            header('Location: listarMateriais.php?msg=material_nao_encontrado');
            exit;
        }
        
        // Verificar se o material está sendo usado em alguma atividade
        $sql_usage = "SELECT COUNT(*) as total FROM material_usage WHERE material_id = ?";
        $stmt_usage = executarQuery($sql_usage, [$material_id]);
        $usage_count = $stmt_usage->fetch()['total'];
        
        if ($usage_count > 0) {
            header('Location: listarMateriais.php?msg=material_com_uso');
            exit;
        }
        
        // Excluir o material
        $sql_delete = "DELETE FROM materials WHERE id = ?";
        $stmt_delete = executarQuery($sql_delete, [$material_id]);
        
        if ($stmt_delete->rowCount() > 0) {
            header('Location: listarMateriais.php?msg=material_excluido');
        } else {
            header('Location: listarMateriais.php?msg=erro_excluir');
        }
    } catch (Exception $e) {
        header('Location: listarMateriais.php?msg=erro_banco');
    }
} else {
    header('Location: listarMateriais.php');
}

exit;
?> 