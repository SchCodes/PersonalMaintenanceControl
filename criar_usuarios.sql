-- =====================================================
-- SISTEMA DE CONTROLE DE MANUTENÇÃO INDUSTRIAL
-- Script para criar/atualizar usuários do sistema
-- =====================================================

USE manutencao_industrial;

-- Verificar se a tabela users existe
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    login VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cargo VARCHAR(100),
    nivel_acesso ENUM('admin', 'tecnico') NOT NULL,
    email VARCHAR(100),
    status BOOLEAN DEFAULT TRUE,
    tema_preferido ENUM('claro', 'escuro') DEFAULT 'claro',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- USUÁRIOS PADRÃO DO SISTEMA
-- =====================================================

-- Usuário Administrador (senha: admin123)
INSERT INTO users (nome, login, senha, cargo, nivel_acesso, email) 
VALUES ('Administrador', 'admin', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Administrador do Sistema', 'admin', 'admin@empresa.com')
ON DUPLICATE KEY UPDATE 
    nome = VALUES(nome),
    senha = VALUES(senha),
    cargo = VALUES(cargo),
    nivel_acesso = VALUES(nivel_acesso),
    email = VALUES(email),
    status = 1;

-- Usuário Técnico Principal (senha: tecnico123)
INSERT INTO users (nome, login, senha, cargo, nivel_acesso, email) 
VALUES ('João Silva', 'tecnico', '$2y$10$xLurmQG5dK36lw2V/3RWAekLQrC5J7CQXSd5JEFHEgw9wr0iRMgpC', 'Técnico de Manutenção', 'tecnico', 'joao.silva@empresa.com')
ON DUPLICATE KEY UPDATE 
    nome = VALUES(nome),
    senha = VALUES(senha),
    cargo = VALUES(cargo),
    nivel_acesso = VALUES(nivel_acesso),
    email = VALUES(email),
    status = 1;

-- =====================================================
-- USUÁRIOS ADICIONAIS DE EXEMPLO (OPCIONAIS)
-- =====================================================

-- Técnico Adicional 1 (senha: maria123)
INSERT INTO users (nome, login, senha, cargo, nivel_acesso, email) 
VALUES ('Maria Santos', 'maria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Técnica de Manutenção', 'tecnico', 'maria.santos@empresa.com')
ON DUPLICATE KEY UPDATE 
    nome = VALUES(nome),
    senha = VALUES(senha),
    cargo = VALUES(cargo),
    nivel_acesso = VALUES(nivel_acesso),
    email = VALUES(email),
    status = 1;

-- Técnico Adicional 2 (senha: carlos123)
INSERT INTO users (nome, login, senha, cargo, nivel_acesso, email) 
VALUES ('Carlos Oliveira', 'carlos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Técnico de Manutenção', 'tecnico', 'carlos.oliveira@empresa.com')
ON DUPLICATE KEY UPDATE 
    nome = VALUES(nome),
    senha = VALUES(senha),
    cargo = VALUES(cargo),
    nivel_acesso = VALUES(nivel_acesso),
    email = VALUES(email),
    status = 1;

-- Supervisor de Manutenção (senha: supervisor123)
INSERT INTO users (nome, login, senha, cargo, nivel_acesso, email) 
VALUES ('Pedro Costa', 'supervisor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Supervisor de Manutenção', 'admin', 'pedro.costa@empresa.com')
ON DUPLICATE KEY UPDATE 
    nome = VALUES(nome),
    senha = VALUES(senha),
    cargo = VALUES(cargo),
    nivel_acesso = VALUES(nivel_acesso),
    email = VALUES(email),
    status = 1;

-- =====================================================
-- VERIFICAÇÃO DOS USUÁRIOS CRIADOS
-- =====================================================

SELECT '=== USUÁRIOS CRIADOS/ATUALIZADOS ===' as info;
SELECT 
    id,
    nome,
    login,
    nivel_acesso,
    CASE status 
        WHEN 1 THEN 'Ativo'
        ELSE 'Inativo'
    END as status,
    email
FROM users 
ORDER BY nivel_acesso DESC, nome;

SELECT '=== RESUMO POR NÍVEL DE ACESSO ===' as info;
SELECT 
    nivel_acesso,
    COUNT(*) as total_usuarios,
    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as usuarios_ativos
FROM users 
GROUP BY nivel_acesso;

-- =====================================================
-- INFORMAÇÕES IMPORTANTES
-- =====================================================
SELECT '=== INFORMAÇÕES DE ACESSO ===' as info;
SELECT 'Admin: admin / admin123' as credenciais;
SELECT 'Técnico: tecnico / tecnico123' as credenciais;
SELECT 'Maria: maria / maria123' as credenciais;
SELECT 'Carlos: carlos / carlos123' as credenciais;
SELECT 'Supervisor: supervisor / supervisor123' as credenciais; 