<?php
session_start();
require_once 'conexaoBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = sanitizar($_POST['login']);
    $senha = $_POST['senha'];
    
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