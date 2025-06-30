<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

// Excluir material
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Verificar se o material existe
    $sql = "SELECT id FROM materials WHERE id = ?";
    $material = executarQuery($sql, [$id])->fetch();
    
    if (!$material) {
        header("Location: listarMateriais.php?msg=material_nao_encontrado");
        exit();
    }
    
    // Verificar se há uso de materiais vinculado
    $sql = "SELECT COUNT(*) as total FROM material_usage WHERE material_id = ?";
    $uso = executarQuery($sql, [$id])->fetch();
    
    if ($uso['total'] > 0) {
        header("Location: listarMateriais.php?msg=material_com_uso");
        exit();
    }
    
    // Excluir material
    $sql = "DELETE FROM materials WHERE id = ?";
    executarQuery($sql, [$id]);
    
    header("Location: listarMateriais.php?msg=material_excluido");
    exit();
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizar($_POST['nome']);
    $codigo = sanitizar($_POST['codigo']);
    $descricao = sanitizar($_POST['descricao']);
    $unidade_medida = sanitizar($_POST['unidade_medida']);
    
    // Processar imagem
    $imagem_blob = null;
    $excluir_imagem = isset($_POST['excluir_imagem']);
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $validacao = validarImagem($_FILES['imagem']);
        if ($validacao === true) {
            $imagem_blob = imagemParaBlob($_FILES['imagem']);
        } else {
            header("Location: formMaterial.php?msg=" . urlencode($validacao));
            exit();
        }
    }
    
    // Validações
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = "Nome é obrigatório";
    }
    
    if (!empty($erros)) {
        $msg = implode(", ", $erros);
        header("Location: formMaterial.php?msg=" . urlencode($msg));
        exit();
    }
    
    // Inserir ou atualizar
    if (isset($_POST['id'])) {
        // Atualizar
        $id = (int)$_POST['id'];
        
        // Construir query dinamicamente baseada na imagem
        if ($excluir_imagem) {
            $sql = "UPDATE materials SET nome = ?, codigo = ?, descricao = ?, unidade_medida = ?, imagem = NULL WHERE id = ?";
            $valores = [$nome, $codigo, $descricao, $unidade_medida, $id];
        } elseif ($imagem_blob) {
            $sql = "UPDATE materials SET nome = ?, codigo = ?, descricao = ?, unidade_medida = ?, imagem = ? WHERE id = ?";
            $valores = [$nome, $codigo, $descricao, $unidade_medida, $imagem_blob, $id];
        } else {
            $sql = "UPDATE materials SET nome = ?, codigo = ?, descricao = ?, unidade_medida = ? WHERE id = ?";
            $valores = [$nome, $codigo, $descricao, $unidade_medida, $id];
        }
        
        executarQuery($sql, $valores);
        header("Location: listarMateriais.php?msg=material_atualizado");
    } else {
        // Inserir novo
        if ($imagem_blob) {
            $sql = "INSERT INTO materials (nome, codigo, descricao, unidade_medida, imagem) VALUES (?, ?, ?, ?, ?)";
            $valores = [$nome, $codigo, $descricao, $unidade_medida, $imagem_blob];
        } else {
            $sql = "INSERT INTO materials (nome, codigo, descricao, unidade_medida) VALUES (?, ?, ?, ?)";
            $valores = [$nome, $codigo, $descricao, $unidade_medida];
        }
        
        executarQuery($sql, $valores);
        header("Location: listarMateriais.php?msg=material_cadastrado");
    }
    exit();
}

// Se chegou aqui, redirecionar
header("Location: listarMateriais.php");
exit();
?> 