<?php
session_start();
require_once 'conexaoBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = sanitizar($_POST['login']);
    $senha = $_POST['senha'];
    
    // VERIFICAÇÃO AUTOMÁTICA: Executar verificar_banco.php apenas na primeira tentativa de login do admin
    if ($login === 'admin') {
        $sql_config = "SELECT ajuste_inicial FROM configuracao WHERE id = 1";
        $stmt_config = executarQuery($sql_config);
        $row_config = $stmt_config->fetch();
        if ($row_config && !$row_config['ajuste_inicial']) {
            // Primeira vez que o admin tenta fazer login - executar verificar_banco.php
            require_once 'verificar_banco.php';
            // Marcar que o ajuste foi feito
            executarQuery("UPDATE configuracao SET ajuste_inicial = 1 WHERE id = 1");
        }
    }
    
    // Buscar usuário
    $sql = "SELECT id, nome, login, senha, nivel_acesso, cargo FROM users WHERE login = ? AND status = 1";
    $stmt = executarQuery($sql, [$login]);
    $usuario = $stmt->fetch();
    
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Login bem-sucedido
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['login'] = $usuario['login'];
        $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
        $_SESSION['cargo'] = $usuario['cargo'];
        $_SESSION['ultimo_acesso'] = time();
        
        header("Location: dashboard.php");
        exit();
    } else {
        // Login falhou
        header("Location: index.php?msg=erro_login");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?> 