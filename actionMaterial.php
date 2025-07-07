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
    $caminho_imagem = null;
    $excluir_imagem = isset($_POST['excluir_imagem']);
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $validacao = validarImagem($_FILES['imagem']);
        if ($validacao === true) {
            $caminho_imagem = salvarImagem($_FILES['imagem'], 'img');
            if (!$caminho_imagem) {
                header("Location: formMaterial.php?msg=Erro ao salvar imagem");
                exit();
            }
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
        
        // Se for excluir imagem, buscar o caminho atual para remover o arquivo
        if ($excluir_imagem) {
            $sql_atual = "SELECT imagem FROM materials WHERE id = ?";
            $material_atual = executarQuery($sql_atual, [$id])->fetch();
            
            // Excluir arquivo físico se existir
            if (!empty($material_atual['imagem']) && file_exists($material_atual['imagem'])) {
                excluirImagem($material_atual['imagem']);
            }
            
            $sql = "UPDATE materials SET nome = ?, codigo = ?, descricao = ?, unidade_medida = ?, imagem = NULL WHERE id = ?";
            $valores = [$nome, $codigo, $descricao, $unidade_medida, $id];
        } elseif ($caminho_imagem) {
            // Se for nova imagem, excluir a antiga primeiro
            $sql_atual = "SELECT imagem FROM materials WHERE id = ?";
            $material_atual = executarQuery($sql_atual, [$id])->fetch();
            
            if (!empty($material_atual['imagem']) && file_exists($material_atual['imagem'])) {
                excluirImagem($material_atual['imagem']);
            }
            
            $sql = "UPDATE materials SET nome = ?, codigo = ?, descricao = ?, unidade_medida = ?, imagem = ? WHERE id = ?";
            $valores = [$nome, $codigo, $descricao, $unidade_medida, $caminho_imagem, $id];
        } else {
            $sql = "UPDATE materials SET nome = ?, codigo = ?, descricao = ?, unidade_medida = ? WHERE id = ?";
            $valores = [$nome, $codigo, $descricao, $unidade_medida, $id];
        }
        
        executarQuery($sql, $valores);
        header("Location: listarMateriais.php?msg=material_atualizado");
    } else {
        // Inserir novo
        if ($caminho_imagem) {
            $sql = "INSERT INTO materials (nome, codigo, descricao, unidade_medida, imagem) VALUES (?, ?, ?, ?, ?)";
            $valores = [$nome, $codigo, $descricao, $unidade_medida, $caminho_imagem];
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