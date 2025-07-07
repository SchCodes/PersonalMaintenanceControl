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

// Função para salvar imagem em arquivo e retornar o caminho
function salvarImagem($arquivo, $pasta = 'img') {
    error_log("DEBUG: salvarImagem chamada para pasta: " . $pasta);
    
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        error_log("DEBUG: Erro no upload: " . $arquivo['error']);
        return null;
    }
    
    // Criar pasta se não existir
    if (!is_dir($pasta)) {
        error_log("DEBUG: Criando pasta: " . $pasta);
        if (!mkdir($pasta, 0755, true)) {
            error_log("DEBUG: Erro ao criar pasta: " . $pasta);
            return null;
        }
    }
    
    // Gerar nome único para o arquivo
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nome_arquivo = uniqid() . '_' . time() . '.' . $extensao;
    $caminho_completo = $pasta . '/' . $nome_arquivo;
    
    error_log("DEBUG: Tentando salvar arquivo em: " . $caminho_completo);
    
    // Mover arquivo para a pasta
    if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
        error_log("DEBUG: Arquivo salvo com sucesso: " . $caminho_completo);
        return $caminho_completo;
    } else {
        error_log("DEBUG: Erro ao mover arquivo para: " . $caminho_completo);
        return null;
    }
}

// Função para excluir imagem antiga
function excluirImagem($caminho) {
    if ($caminho && file_exists($caminho)) {
        unlink($caminho);
        return true;
    }
    return false;
}

// Função para obter URL da imagem
function obterUrlImagem($caminho) {
    if (!$caminho) {
        return null;
    }
    
    // Se já é uma URL completa, retorna como está
    if (filter_var($caminho, FILTER_VALIDATE_URL)) {
        return $caminho;
    }
    
    // Se é um caminho relativo, retorna a URL
    return $caminho;
}
?> 