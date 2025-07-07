<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

// Verificar se é admin
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

// Verificar se foi passado um ID
if (!isset($_GET['id'])) {
    header('Location: listarAtividades.php?msg=id_nao_fornecido');
    exit;
}

$id = (int)$_GET['id'];

// Verificar se a atividade existe
$sql = "SELECT id, titulo, usuario_id, imagem_antes, imagem_depois FROM maintenance_activities WHERE id = ?";
$atividade = executarQuery($sql, [$id])->fetch();

if (!$atividade) {
    header('Location: listarAtividades.php?msg=atividade_nao_encontrada');
    exit;
}

// Excluir imagens associadas se existirem
if (!empty($atividade['imagem_antes']) && file_exists($atividade['imagem_antes'])) {
    excluirImagem($atividade['imagem_antes']);
}

if (!empty($atividade['imagem_depois']) && file_exists($atividade['imagem_depois'])) {
    excluirImagem($atividade['imagem_depois']);
}

// Excluir registros de uso de materiais associados
try {
    $sql_delete_materiais = "DELETE FROM material_usage WHERE atividade_id = ?";
    executarQuery($sql_delete_materiais, [$id]);
} catch (Exception $e) {
    // Log do erro, mas continua com a exclusão da atividade
}

// Excluir atividade
try {
    $sql = "DELETE FROM maintenance_activities WHERE id = ?";
    $stmt = executarQuery($sql, [$id]);
    
    if ($stmt->rowCount() > 0) {
        header('Location: listarAtividades.php?msg=atividade_excluida');
    } else {
        header('Location: listarAtividades.php?msg=erro_excluir_atividade');
    }
} catch (Exception $e) {
    header('Location: listarAtividades.php?msg=erro_banco');
}

exit;
?> 