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
    $equipamento_id = (int)$_GET['id'];
    
    try {
        // Verificar se o equipamento existe
        $sql_check = "SELECT id, nome FROM equipment WHERE id = ?";
        $stmt_check = executarQuery($sql_check, [$equipamento_id]);
        $equipamento = $stmt_check->fetch();
        
        if (!$equipamento) {
            header('Location: listarEquipamentos.php?msg=equipamento_nao_encontrado');
            exit;
        }
        
        // Verificar se o equipamento está sendo usado em alguma atividade
        $sql_usage = "SELECT COUNT(*) as total FROM maintenance_activities WHERE equipamento_id = ?";
        $stmt_usage = executarQuery($sql_usage, [$equipamento_id]);
        $usage_count = $stmt_usage->fetch()['total'];
        
        if ($usage_count > 0) {
            header('Location: listarEquipamentos.php?msg=equipamento_com_atividades');
            exit;
        }
        
        // Excluir o equipamento
        $sql_delete = "DELETE FROM equipment WHERE id = ?";
        $stmt_delete = executarQuery($sql_delete, [$equipamento_id]);
        
        if ($stmt_delete->rowCount() > 0) {
            header('Location: listarEquipamentos.php?msg=equipamento_excluido');
        } else {
            header('Location: listarEquipamentos.php?msg=erro_excluir');
        }
    } catch (Exception $e) {
        header('Location: listarEquipamentos.php?msg=erro_banco');
    }
} else {
    header('Location: listarEquipamentos.php');
}

exit;
?> 