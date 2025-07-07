<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

// Verificar se é admin
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

// Verificar se foi passado um ID de usuário
if (!isset($_GET['usuario_id'])) {
    header('Location: listarUsuarios.php?msg=id_usuario_nao_fornecido');
    exit;
}

$usuario_id = (int)$_GET['usuario_id'];

// Verificar se o usuário existe
$sql_usuario = "SELECT id, nome FROM users WHERE id = ?";
$usuario = executarQuery($sql_usuario, [$usuario_id])->fetch();

if (!$usuario) {
    header('Location: listarUsuarios.php?msg=usuario_nao_encontrado');
    exit;
}

// Verificar se não é o próprio usuário logado
if ($usuario_id === $_SESSION['usuario_id']) {
    header('Location: listarUsuarios.php?msg=nao_pode_excluir_proprio_usuario');
    exit;
}

// Buscar todas as atividades do usuário
$sql_atividades = "SELECT id, titulo, imagem_antes, imagem_depois FROM maintenance_activities WHERE usuario_id = ?";
$atividades = executarQuery($sql_atividades, [$usuario_id])->fetchAll();

$atividades_excluidas = 0;
$erros = 0;

// Excluir cada atividade
foreach ($atividades as $atividade) {
    try {
        // Excluir imagens associadas se existirem
        if (!empty($atividade['imagem_antes']) && file_exists($atividade['imagem_antes'])) {
            excluirImagem($atividade['imagem_antes']);
        }
        
        if (!empty($atividade['imagem_depois']) && file_exists($atividade['imagem_depois'])) {
            excluirImagem($atividade['imagem_depois']);
        }
        
        // Excluir registros de uso de materiais associados
        $sql_delete_materiais = "DELETE FROM material_usage WHERE atividade_id = ?";
        executarQuery($sql_delete_materiais, [$atividade['id']]);
        
        // Excluir atividade
        $sql_delete_atividade = "DELETE FROM maintenance_activities WHERE id = ?";
        $stmt = executarQuery($sql_delete_atividade, [$atividade['id']]);
        
        if ($stmt->rowCount() > 0) {
            $atividades_excluidas++;
        } else {
            $erros++;
        }
        
    } catch (Exception $e) {
        $erros++;
    }
}

// Redirecionar com mensagem de resultado
if ($erros > 0) {
    header("Location: listarUsuarios.php?msg=atividades_excluidas_com_erros&total={$atividades_excluidas}&erros={$erros}");
} else {
    header("Location: listarUsuarios.php?msg=atividades_excluidas_sucesso&total={$atividades_excluidas}");
}

exit;
?> 