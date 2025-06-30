<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

// Excluir equipamento
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Verificar se o equipamento existe
    $sql = "SELECT id FROM equipment WHERE id = ?";
    $equipamento = executarQuery($sql, [$id])->fetch();
    
    if (!$equipamento) {
        header("Location: listarEquipamentos.php?msg=equipamento_nao_encontrado");
        exit();
    }
    
    // Verificar se há atividades vinculadas
    $sql = "SELECT COUNT(*) as total FROM maintenance_activities WHERE equipamento_id = ?";
    $atividades = executarQuery($sql, [$id])->fetch();
    
    if ($atividades['total'] > 0) {
        header("Location: listarEquipamentos.php?msg=equipamento_com_atividades");
        exit();
    }
    
    // Excluir equipamento
    $sql = "DELETE FROM equipment WHERE id = ?";
    executarQuery($sql, [$id]);
    
    header("Location: listarEquipamentos.php?msg=equipamento_excluido");
    exit();
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizar($_POST['nome']);
    $codigo = sanitizar($_POST['codigo']);
    $tipo = sanitizar($_POST['tipo']);
    $localizacao = sanitizar($_POST['localizacao']);
    $area_planta = sanitizar($_POST['area_planta']);
    $status = sanitizar($_POST['status']);
    $descricao = sanitizar($_POST['descricao']);
    
    // Validações
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = "Nome é obrigatório";
    }
    
    if (empty($codigo)) {
        $erros[] = "Código é obrigatório";
    }
    
    // Verificar se código já existe (exceto na edição)
    if (!isset($_POST['id'])) {
        $sql = "SELECT id FROM equipment WHERE codigo = ?";
        $existe = executarQuery($sql, [$codigo])->fetch();
        if ($existe) {
            $erros[] = "Código já existe";
        }
    }
    
    if (!empty($erros)) {
        $msg = implode(", ", $erros);
        header("Location: formEquipamento.php?msg=" . urlencode($msg));
        exit();
    }
    
    // Processar imagem se enviada
    $imagem_blob = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $validacao = validarImagem($_FILES['imagem']);
        if ($validacao === true) {
            $imagem_blob = imagemParaBlob($_FILES['imagem']);
        } else {
            header("Location: formEquipamento.php?msg=" . urlencode($validacao));
            exit();
        }
    }
    
    // Inserir ou atualizar
    if (isset($_POST['id'])) {
        // Atualizar
        $id = (int)$_POST['id'];
        
        if ($imagem_blob) {
            // Atualizar com nova imagem
            $sql = "UPDATE equipment SET nome = ?, codigo = ?, tipo = ?, localizacao = ?, area_planta = ?, status = ?, descricao = ?, imagem = ? WHERE id = ?";
            executarQuery($sql, [$nome, $codigo, $tipo, $localizacao, $area_planta, $status, $descricao, $imagem_blob, $id]);
        } else {
            // Atualizar sem alterar imagem
            $sql = "UPDATE equipment SET nome = ?, codigo = ?, tipo = ?, localizacao = ?, area_planta = ?, status = ?, descricao = ? WHERE id = ?";
            executarQuery($sql, [$nome, $codigo, $tipo, $localizacao, $area_planta, $status, $descricao, $id]);
        }
        
        header("Location: listarEquipamentos.php?msg=equipamento_atualizado");
    } else {
        // Inserir novo
        $sql = "INSERT INTO equipment (nome, codigo, tipo, localizacao, area_planta, status, descricao, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        executarQuery($sql, [$nome, $codigo, $tipo, $localizacao, $area_planta, $status, $descricao, $imagem_blob]);
        
        header("Location: listarEquipamentos.php?msg=equipamento_cadastrado");
    }
    exit();
}

// Se chegou aqui, redirecionar
header("Location: listarEquipamentos.php");
exit();
?> 