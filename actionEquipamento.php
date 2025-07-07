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
    
    // Processar imagem
    $caminho_imagem = null;
    $excluir_imagem = isset($_POST['excluir_imagem']);
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $validacao = validarImagem($_FILES['imagem']);
        if ($validacao === true) {
            $caminho_imagem = salvarImagem($_FILES['imagem'], 'img');
            if (!$caminho_imagem) {
                header("Location: formEquipamento.php?msg=Erro ao salvar imagem");
                exit();
            }
        } else {
            header("Location: formEquipamento.php?msg=" . urlencode($validacao));
            exit();
        }
    }
    
    // Inserir ou atualizar
    if (isset($_POST['id'])) {
        // Atualizar
        $id = (int)$_POST['id'];
        
        // Se for excluir imagem, buscar o caminho atual para remover o arquivo
        if ($excluir_imagem) {
            $sql_atual = "SELECT imagem FROM equipment WHERE id = ?";
            $equipamento_atual = executarQuery($sql_atual, [$id])->fetch();
            
            // Excluir arquivo físico se existir
            if (!empty($equipamento_atual['imagem']) && file_exists($equipamento_atual['imagem'])) {
                excluirImagem($equipamento_atual['imagem']);
            }
            
            $sql = "UPDATE equipment SET nome = ?, codigo = ?, tipo = ?, localizacao = ?, area_planta = ?, status = ?, descricao = ?, imagem = NULL WHERE id = ?";
            $valores = [$nome, $codigo, $tipo, $localizacao, $area_planta, $status, $descricao, $id];
        } elseif ($caminho_imagem) {
            // Se for nova imagem, excluir a antiga primeiro
            $sql_atual = "SELECT imagem FROM equipment WHERE id = ?";
            $equipamento_atual = executarQuery($sql_atual, [$id])->fetch();
            
            if (!empty($equipamento_atual['imagem']) && file_exists($equipamento_atual['imagem'])) {
                excluirImagem($equipamento_atual['imagem']);
            }
            
            $sql = "UPDATE equipment SET nome = ?, codigo = ?, tipo = ?, localizacao = ?, area_planta = ?, status = ?, descricao = ?, imagem = ? WHERE id = ?";
            $valores = [$nome, $codigo, $tipo, $localizacao, $area_planta, $status, $descricao, $caminho_imagem, $id];
        } else {
            $sql = "UPDATE equipment SET nome = ?, codigo = ?, tipo = ?, localizacao = ?, area_planta = ?, status = ?, descricao = ? WHERE id = ?";
            $valores = [$nome, $codigo, $tipo, $localizacao, $area_planta, $status, $descricao, $id];
        }
        
        executarQuery($sql, $valores);
        header("Location: listarEquipamentos.php?msg=equipamento_atualizado");
    } else {
        // Inserir novo
        if ($caminho_imagem) {
            $sql = "INSERT INTO equipment (nome, codigo, tipo, localizacao, area_planta, status, descricao, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $valores = [$nome, $codigo, $tipo, $localizacao, $area_planta, $status, $descricao, $caminho_imagem];
        } else {
            $sql = "INSERT INTO equipment (nome, codigo, tipo, localizacao, area_planta, status, descricao) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $valores = [$nome, $codigo, $tipo, $localizacao, $area_planta, $status, $descricao];
        }
        
        executarQuery($sql, $valores);
        header("Location: listarEquipamentos.php?msg=equipamento_cadastrado");
    }
    exit();
}

// Se chegou aqui, redirecionar
header("Location: listarEquipamentos.php");
exit();
?> 