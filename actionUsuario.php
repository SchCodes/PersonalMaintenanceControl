<?php
require_once 'validarSessao.php';
verificarPermissao('admin');
require_once 'conexaoBD.php';

// Excluir usuário
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Verificar se não é o próprio usuário logado
    if ($id === $_SESSION['usuario_id']) {
        header("Location: listarUsuarios.php?msg=nao_pode_excluir_proprio_usuario");
        exit();
    }
    
    // Verificar se o usuário existe
    $sql = "SELECT id FROM users WHERE id = ?";
    $usuario = executarQuery($sql, [$id])->fetch();
    
    if (!$usuario) {
        header("Location: listarUsuarios.php?msg=usuario_nao_encontrado");
        exit();
    }
    
    // Excluir usuário
    $sql = "DELETE FROM users WHERE id = ?";
    executarQuery($sql, [$id]);
    
    header("Location: listarUsuarios.php?msg=usuario_excluido");
    exit();
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizar($_POST['nome']);
    $login = sanitizar($_POST['login']);
    $email = sanitizar($_POST['email']);
    $cargo = sanitizar($_POST['cargo']);
    $nivel_acesso = sanitizar($_POST['nivel_acesso']);
    $status = (int)$_POST['status'];
    
    // Validações
    $erros = [];
    
    if (empty($nome)) {
        $erros[] = "Nome é obrigatório";
    }
    
    if (empty($login)) {
        $erros[] = "Login é obrigatório";
    }
    
    if (empty($nivel_acesso)) {
        $erros[] = "Nível de acesso é obrigatório";
    }
    
    // Verificar se login já existe (exceto na edição)
    if (!isset($_POST['id'])) {
        $sql = "SELECT id FROM users WHERE login = ?";
        $existe = executarQuery($sql, [$login])->fetch();
        if ($existe) {
            $erros[] = "Login já existe";
        }
    }
    
    if (!empty($erros)) {
        $msg = implode(", ", $erros);
        header("Location: formUsuario.php?msg=" . urlencode($msg));
        exit();
    }
    
    // Inserir ou atualizar
    if (isset($_POST['id'])) {
        // Atualizar
        $id = (int)$_POST['id'];
        $senha = $_POST['senha'];
        
        if (!empty($senha)) {
            // Atualizar com nova senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET nome = ?, login = ?, email = ?, cargo = ?, nivel_acesso = ?, status = ?, senha = ? WHERE id = ?";
            executarQuery($sql, [$nome, $login, $email, $cargo, $nivel_acesso, $status, $senha_hash, $id]);
        } else {
            // Atualizar sem alterar senha
            $sql = "UPDATE users SET nome = ?, login = ?, email = ?, cargo = ?, nivel_acesso = ?, status = ? WHERE id = ?";
            executarQuery($sql, [$nome, $login, $email, $cargo, $nivel_acesso, $status, $id]);
        }
        
        header("Location: listarUsuarios.php?msg=usuario_atualizado");
    } else {
        // Inserir novo
        $senha = $_POST['senha'];
        
        if (empty($senha)) {
            header("Location: formUsuario.php?msg=senha_obrigatoria");
            exit();
        }
        
        if (strlen($senha) < 6) {
            header("Location: formUsuario.php?msg=senha_minimo_6_caracteres");
            exit();
        }
        
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (nome, login, senha, email, cargo, nivel_acesso, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        executarQuery($sql, [$nome, $login, $senha_hash, $email, $cargo, $nivel_acesso, $status]);
        
        header("Location: listarUsuarios.php?msg=usuario_cadastrado");
    }
    exit();
}

// Se chegou aqui, redirecionar
header("Location: listarUsuarios.php");
exit();
?> 