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
    $status = $_GET['status'];
    
    try {
        $sql = "UPDATE equipment SET status = ? WHERE id = ?";
        $stmt = executarQuery($sql, [$status, $id]);
        
        if ($stmt->rowCount() > 0) {
            $acao = $status === 'ativo' ? 'ativado' : 'desativado';
            header('Location: listarEquipamentos.php?msg=equipamento_' . $acao);
        } else {
            header('Location: listarEquipamentos.php?msg=erro_alterar_status');
        }
    } catch (Exception $e) {
        header('Location: listarEquipamentos.php?msg=erro_banco');
    }
} else {
    header('Location: listarEquipamentos.php');
}

exit;
?> 