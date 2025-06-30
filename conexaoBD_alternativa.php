<?php
// Configurações alternativas do banco de dados
// Tente diferentes configurações se a primeira não funcionar

// Configuração 1: Padrão XAMPP
$host = 'localhost';
$dbname = 'manutencao_industrial';
$username = 'root';
$password = '';

// Se a configuração 1 não funcionar, tente estas alternativas:
// Configuração 2: IP local
// $host = '127.0.0.1';

// Configuração 3: Com porta específica (se necessário)
// $host = 'localhost:3306';

// Configuração 4: Com senha (se você definiu uma)
// $password = 'sua_senha_aqui';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "<!-- Conexão bem-sucedida -->"; // Comentário HTML para debug
} catch(PDOException $e) {
    // Se falhar, tenta com IP local
    try {
        $host = '127.0.0.1';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        echo "<!-- Conexão bem-sucedida com IP local -->";
    } catch(PDOException $e2) {
        die("Erro na conexão: " . $e2->getMessage() . "<br>Verifique se o banco 'manutencao_industrial' existe e o XAMPP está rodando.");
    }
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