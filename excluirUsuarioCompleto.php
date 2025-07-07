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
    header('Location: listarUsuarios.php?msg=id_nao_fornecido');
    exit;
}

$id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// Verificar se não é o próprio usuário logado
if ($id === $usuario_id) {
    header('Location: listarUsuarios.php?msg=nao_pode_excluir_proprio_usuario');
    exit;
}

// Verificar se o usuário existe
$sql = "SELECT id, nome FROM users WHERE id = ?";
$usuario = executarQuery($sql, [$id])->fetch();

if (!$usuario) {
    header('Location: listarUsuarios.php?msg=usuario_nao_encontrado');
    exit;
}

// Verificar se o usuário tem atividades vinculadas
$sql_atividades = "SELECT COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ?";
$atividades = executarQuery($sql_atividades, [$id])->fetch();

if ($atividades['total'] > 0) {
    // Excluir atividades primeiro
    $sql_buscar_atividades = "SELECT id, titulo, imagem_antes, imagem_depois FROM maintenance_activities WHERE usuario_id = ?";
    $todas_atividades = executarQuery($sql_buscar_atividades, [$id])->fetchAll();
    
    $atividades_excluidas = 0;
    
    foreach ($todas_atividades as $atividade) {
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
            executarQuery($sql_delete_atividade, [$atividade['id']]);
            
            $atividades_excluidas++;
            
        } catch (Exception $e) {
            // Continua mesmo com erro
        }
    }
    
    // Agora excluir o usuário
    try {
        $sql_delete_usuario = "DELETE FROM users WHERE id = ?";
        $stmt = executarQuery($sql_delete_usuario, [$id]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: listarUsuarios.php?msg=usuario_excluido_com_atividades&atividades={$atividades_excluidas}");
        } else {
            header('Location: listarUsuarios.php?msg=erro_excluir_usuario');
        }
    } catch (Exception $e) {
        header('Location: listarUsuarios.php?msg=erro_banco');
    }
    
} else {
    // Usuário não tem atividades, excluir diretamente
    try {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = executarQuery($sql, [$id]);
        
        if ($stmt->rowCount() > 0) {
            header('Location: listarUsuarios.php?msg=usuario_excluido');
        } else {
            header('Location: listarUsuarios.php?msg=erro_excluir_usuario');
        }
    } catch (Exception $e) {
        header('Location: listarUsuarios.php?msg=erro_banco');
    }
}

exit;
?> 