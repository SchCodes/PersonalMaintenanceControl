<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'manutencao_industrial';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Função para executar queries com segurança
function executarQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        die("Erro na query: " . $e->getMessage());
    }
}

// Função para validar e sanitizar dados
function sanitizar($dados) {
    return htmlspecialchars(strip_tags(trim($dados)));
}

// Função para validar imagem
function validarImagem($arquivo) {
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png'];
    $tamanhoMaximo = 10 * 1024 * 1024; // 10MB
    
    if (!in_array($arquivo['type'], $tiposPermitidos)) {
        return "Tipo de arquivo não permitido. Use apenas JPG, JPEG ou PNG.";
    }
    
    if ($arquivo['size'] > $tamanhoMaximo) {
        return "Arquivo muito grande. Máximo 10MB.";
    }
    
    return true;
}

// Função para converter imagem para BLOB
function imagemParaBlob($arquivo) {
    if ($arquivo['error'] === UPLOAD_ERR_OK) {
        return file_get_contents($arquivo['tmp_name']);
    }
    return null;
}
?> 