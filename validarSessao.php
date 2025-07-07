<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Verificar se a sessão não expirou (sem expiração para MVP)
// if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso']) > 3600) {
//     session_destroy();
//     header("Location: index.php?msg=sessao_expirada");
//     exit();
// }

$_SESSION['ultimo_acesso'] = time();

// Função para verificar se é admin
function isAdmin() {
    return isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'admin';
}

// Função para verificar se é técnico
function isTecnico() {
    return isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'tecnico';
}

// Função para verificar permissão
function verificarPermissao($nivelNecessario = 'tecnico') {
    if ($nivelNecessario === 'admin' && !isAdmin()) {
        header("Location: index.php?msg=sem_permissao");
        exit();
    }
}
?> 