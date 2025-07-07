<?php
echo "<h2>Verificação Completa do Banco de Dados</h2>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=manutencao_industrial;charset=utf8", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexão estabelecida<br><br>";
} catch(PDOException $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
    exit;
}

// 1. Verificar estrutura da tabela users
echo "<h3>1. Estrutura da Tabela Users</h3>";
$sql = "DESCRIBE users";
$stmt = $pdo->query($sql);
$colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
foreach ($colunas as $coluna) {
    echo "<tr>";
    echo "<td>{$coluna['Field']}</td>";
    echo "<td>{$coluna['Type']}</td>";
    echo "<td>{$coluna['Null']}</td>";
    echo "<td>{$coluna['Key']}</td>";
    echo "<td>{$coluna['Default']}</td>";
    echo "<td>{$coluna['Extra']}</td>";
    echo "</tr>";
}
echo "</table><br>";

// 2. Verificar dados dos usuários
echo "<h3>2. Dados dos Usuários</h3>";
$sql = "SELECT * FROM users";
$stmt = $pdo->query($sql);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($usuarios) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Login</th><th>Status</th><th>Nível</th><th>Hash (início)</th></tr>";
    foreach ($usuarios as $usuario) {
        echo "<tr>";
        echo "<td>{$usuario['id']}</td>";
        echo "<td>{$usuario['nome']}</td>";
        echo "<td>{$usuario['login']}</td>";
        echo "<td>{$usuario['status']}</td>";
        echo "<td>{$usuario['nivel_acesso']}</td>";
        echo "<td>" . substr($usuario['senha'], 0, 20) . "...</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "❌ Nenhum usuário encontrado<br><br>";
}

// 3. Testar queries específicas
echo "<h3>3. Teste de Queries</h3>";

// Query 1: Buscar admin
$sql = "SELECT * FROM users WHERE login = 'admin'";
$stmt = $pdo->query($sql);
$admin = $stmt->fetch();
echo "Query 1 - Buscar admin: " . ($admin ? "✅ Encontrado" : "❌ Não encontrado") . "<br>";

// Query 2: Buscar admin com status = 1
$sql = "SELECT * FROM users WHERE login = 'admin' AND status = 1";
$stmt = $pdo->query($sql);
$admin_status = $stmt->fetch();
echo "Query 2 - Buscar admin com status=1: " . ($admin_status ? "✅ Encontrado" : "❌ Não encontrado") . "<br>";

// Query 3: Buscar técnico
$sql = "SELECT * FROM users WHERE login = 'tecnico'";
$stmt = $pdo->query($sql);
$tecnico = $stmt->fetch();
echo "Query 3 - Buscar técnico: " . ($tecnico ? "✅ Encontrado" : "❌ Não encontrado") . "<br>";

// Query 4: Buscar técnico com status = 1
$sql = "SELECT * FROM users WHERE login = 'tecnico' AND status = 1";
$stmt = $pdo->query($sql);
$tecnico_status = $stmt->fetch();
echo "Query 4 - Buscar técnico com status=1: " . ($tecnico_status ? "✅ Encontrado" : "❌ Não encontrado") . "<br><br>";

// 4. Testar password_verify
echo "<h3>4. Teste de Verificação de Senhas</h3>";

if ($admin) {
    echo "Testando senha do admin:<br>";
    $senhas_teste = ['admin123', 'admin', '123', 'password'];
    foreach ($senhas_teste as $senha) {
        $resultado = password_verify($senha, $admin['senha']);
        echo "- '$senha': " . ($resultado ? "✅ Válida" : "❌ Inválida") . "<br>";
    }
}

if ($tecnico) {
    echo "<br>Testando senha do técnico:<br>";
    $senhas_teste = ['tecnico123', 'tecnico', '123', 'password'];
    foreach ($senhas_teste as $senha) {
        $resultado = password_verify($senha, $tecnico['senha']);
        echo "- '$senha': " . ($resultado ? "✅ Válida" : "❌ Inválida") . "<br>";
    }
}

// 5. Recriar senhas se necessário
echo "<h3>5. Recriando Senhas</h3>";

$hash_admin = password_hash('admin123', PASSWORD_DEFAULT);
$hash_tecnico = password_hash('tecnico123', PASSWORD_DEFAULT);

$sql = "UPDATE users SET senha = ? WHERE login = 'admin'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$hash_admin]);
echo "✅ Senha do admin recriada<br>";

$sql = "UPDATE users SET senha = ? WHERE login = 'tecnico'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$hash_tecnico]);
echo "✅ Senha do técnico recriada<br><br>";

// 6. Teste final
echo "<h3>6. Teste Final</h3>";
$sql = "SELECT * FROM users WHERE login = 'admin' AND status = 1";
$stmt = $pdo->query($sql);
$admin_final = $stmt->fetch();

if ($admin_final && password_verify('admin123', $admin_final['senha'])) {
    echo "✅ Login do admin funcionando!<br>";
} else {
    echo "❌ Login do admin ainda com problema<br>";
}

$sql = "SELECT * FROM users WHERE login = 'tecnico' AND status = 1";
$stmt = $pdo->query($sql);
$tecnico_final = $stmt->fetch();

if ($tecnico_final && password_verify('tecnico123', $tecnico_final['senha'])) {
    echo "✅ Login do técnico funcionando!<br>";
} else {
    echo "❌ Login do técnico ainda com problema<br>";
}

echo "<br><h3>7. Instruções</h3>";
echo "<p>Agora tente fazer login com:</p>";
echo "<ul>";
echo "<li><strong>Admin:</strong> login=admin, senha=admin123</li>";
echo "<li><strong>Técnico:</strong> login=tecnico, senha=tecnico123</li>";
echo "</ul>";
echo "<p><a href='index.php'>Ir para o login</a></p>";
?> 