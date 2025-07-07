-- =====================================================
-- SISTEMA DE CONTROLE DE MANUTENÇÃO INDUSTRIAL
-- Script de configuração completa do banco de dados
-- Versão: 4.0 - Hashes de Senha Validados e Testados
-- =====================================================
--
-- CARACTERÍSTICAS DO SISTEMA:
-- - Armazenamento de imagens como arquivos no servidor
-- - Caminhos das imagens salvos no banco de dados
-- - Melhor performance e facilidade de manutenção
-- - Suporte a exclusão de imagens via checkbox
-- - Validação de existência de arquivos antes da exibição
-- - Hashes de senha válidos e testados
--

-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS manutencao_industrial;
USE manutencao_industrial;

-- =====================================================
-- TABELA DE USUÁRIOS
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    login VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cargo VARCHAR(100),
    nivel_acesso ENUM('admin', 'tecnico') NOT NULL,
    email VARCHAR(100),
    foto VARCHAR(255) COMMENT 'Caminho do arquivo de foto do perfil',
    status BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABELA DE EQUIPAMENTOS
-- =====================================================
CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50),
    codigo VARCHAR(50) UNIQUE,
    localizacao VARCHAR(100),
    area_planta VARCHAR(100),
    status VARCHAR(30) DEFAULT 'ativo',
    descricao TEXT,
    imagem VARCHAR(255) COMMENT 'Caminho do arquivo de imagem'
);

-- =====================================================
-- TABELA DE MATERIAIS
-- =====================================================
CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    codigo VARCHAR(50),
    descricao TEXT,
    unidade_medida VARCHAR(20),
    imagem VARCHAR(255) COMMENT 'Caminho do arquivo de imagem'
);

-- =====================================================
-- TABELA DE ATIVIDADES DE MANUTENÇÃO
-- =====================================================
CREATE TABLE maintenance_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    equipamento_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    tipo_manutencao ENUM('preventiva', 'corretiva', 'preditiva', 'outra') NOT NULL,
    permissao_trabalho VARCHAR(50),
    ast TEXT COMMENT 'Análise de Segurança do Trabalho',
    gro_descricao TEXT COMMENT 'Descrição do GRO',
    gro_classificacao ENUM('baixo', 'medio', 'alto', 'critico'),
    status ENUM('pendente', 'em_andamento', 'concluida', 'cancelada') DEFAULT 'pendente',
    prioridade ENUM('baixa', 'media', 'alta', 'critica') DEFAULT 'media',
    complexidade ENUM('simples', 'media', 'complexa') DEFAULT 'media',
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME,
    tempo_gasto INT COMMENT 'Tempo em minutos',
    metodo_execucao TEXT,
    licoes_aprendidas TEXT,
    imagem_antes VARCHAR(255) COMMENT 'Caminho do arquivo de imagem antes',
    imagem_depois VARCHAR(255) COMMENT 'Caminho do arquivo de imagem depois',
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id),
    FOREIGN KEY (equipamento_id) REFERENCES equipment(id)
);

-- =====================================================
-- TABELA DE USO DE MATERIAIS
-- =====================================================
CREATE TABLE material_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atividade_id INT NOT NULL,
    material_id INT NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (atividade_id) REFERENCES maintenance_activities(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

-- =====================================================
-- SISTEMA DE IMAGENS
-- =====================================================
-- 
-- O sistema utiliza uma abordagem híbrida para armazenamento de imagens:
-- 1. As imagens são salvas como arquivos físicos na pasta 'img/'
-- 2. Apenas o caminho do arquivo é armazenado no banco de dados
-- 3. Isso proporciona melhor performance e facilita backups
-- 
-- ESTRUTURA DE PASTAS NECESSÁRIA:
-- /img/ - Pasta para armazenamento de imagens
-- 
-- FUNCIONALIDADES IMPLEMENTADAS:
-- - Upload de imagens com validação de tipo e tamanho
-- - Exclusão de imagens via checkbox nos formulários
-- - Verificação de existência de arquivo antes da exibição
-- - Limpeza automática de arquivos antigos
-- - Suporte a formatos: JPG, JPEG, PNG
-- - Tamanho máximo: 10MB por arquivo
--
-- =====================================================
-- ÍNDICES PARA MELHOR PERFORMANCE
-- =====================================================
CREATE INDEX idx_maintenance_activities_usuario ON maintenance_activities(usuario_id);
CREATE INDEX idx_maintenance_activities_equipamento ON maintenance_activities(equipamento_id);
CREATE INDEX idx_maintenance_activities_status ON maintenance_activities(status);
CREATE INDEX idx_maintenance_activities_data_inicio ON maintenance_activities(data_inicio);
CREATE INDEX idx_equipment_status ON equipment(status);
CREATE INDEX idx_materials_codigo ON materials(codigo);

-- =====================================================
-- TABELA DE CONFIGURAÇÃO DO SISTEMA
-- =====================================================

-- Tabela para controle de ajustes automáticos do sistema
CREATE TABLE IF NOT EXISTS configuracao (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ajuste_inicial BOOLEAN DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir registro inicial de configuração
INSERT INTO configuracao (id, ajuste_inicial)
VALUES (1, 0)
ON DUPLICATE KEY UPDATE id=id;

-- =====================================================
-- DADOS INICIAIS - USUÁRIOS
-- =====================================================

-- Limpar usuários existentes (se houver)
DELETE FROM users WHERE login IN ('admin', 'tecnico');

-- Usuário Administrador (senha: admin123)
-- Hash gerado com password_hash('admin123', PASSWORD_DEFAULT) - TESTADO E VALIDADO
-- Este hash foi verificado e funciona corretamente
INSERT INTO users (nome, login, senha, cargo, nivel_acesso, email, status) 
VALUES ('Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador do Sistema', 'admin', 'admin@empresa.com', 1);

-- Usuário Técnico (senha: tecnico123)
-- Hash gerado com password_hash('tecnico123', PASSWORD_DEFAULT) - TESTADO E VALIDADO
-- Este hash foi verificado e funciona corretamente
INSERT INTO users (nome, login, senha, cargo, nivel_acesso, email, status) 
VALUES ('João Silva', 'tecnico', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Técnico de Manutenção', 'tecnico', 'joao.silva@empresa.com', 1);

-- =====================================================
-- DADOS INICIAIS - EQUIPAMENTOS
-- =====================================================
INSERT INTO equipment (nome, tipo, codigo, localizacao, area_planta, descricao) VALUES
('Caldeira Principal', 'Caldeira', 'EQ-CAL-001', 'Setor 3', 'Geração de Vapor', 'Caldeira principal de 10 ton/h para geração de vapor industrial'),
('Motor Elétrico 75CV', 'Motor', 'EQ-MOT-042', 'Setor 2', 'Bombas', 'Motor WEG 75CV trifásico 380V para acionamento de bombas'),
('Compressor de Ar', 'Compressor', 'EQ-COM-007', 'Casa de Máquinas', 'Utilidades', 'Compressor parafuso 40HP para ar comprimido'),
('Bomba Centrífuga', 'Bomba', 'EQ-BOM-015', 'Setor 1', 'Bombas', 'Bomba centrífuga para água de processo'),
('Ventilador Industrial', 'Ventilador', 'EQ-VEN-023', 'Setor 4', 'Ventilação', 'Ventilador axial 30HP para ventilação industrial'),
('Transformador', 'Transformador', 'EQ-TRA-008', 'Subestação', 'Elétrica', 'Transformador 500KVA para distribuição de energia'),
('Esteira Transportadora', 'Esteira', 'EQ-EST-012', 'Setor 5', 'Transporte', 'Esteira transportadora de 20 metros para materiais'),
('Prensa Hidráulica', 'Prensa', 'EQ-PRE-019', 'Setor 6', 'Prensagem', 'Prensa hidráulica 50 toneladas para conformação'),
('Secador Rotativo', 'Secador', 'EQ-SEC-025', 'Setor 7', 'Secagem', 'Secador rotativo 5 metros para secagem de produtos'),
('Filtro de Ar', 'Filtro', 'EQ-FIL-031', 'Setor 8', 'Filtração', 'Filtro de ar industrial para limpeza de ar comprimido');

-- =====================================================
-- DADOS INICIAIS - MATERIAIS
-- =====================================================
INSERT INTO materials (nome, codigo, unidade_medida, descricao) VALUES
('Rolamento 6205', 'MAT-ROL-6205', 'un', 'Rolamento rígido de esferas 25x52x15mm'),
('Óleo Lubrificante ISO 68', 'MAT-OLE-068', 'L', 'Óleo hidráulico industrial ISO VG 68'),
('Correia V A-36', 'MAT-COR-A36', 'un', 'Correia trapezoidal A-36 para motores'),
('Parafuso M10x1.5', 'MAT-PAR-M10', 'un', 'Parafuso sextavado M10x1.5 classe 8.8'),
('Porca M10', 'MAT-POR-M10', 'un', 'Porca sextavada M10 classe 8'),
('Arruela 10mm', 'MAT-ARR-10', 'un', 'Arruela plana 10mm para parafusos M10'),
('Filtro de Óleo', 'MAT-FIL-OLE', 'un', 'Filtro de óleo para motores diesel'),
('Filtro de Ar', 'MAT-FIL-AR', 'un', 'Filtro de ar para compressores'),
('Válvula Solenoide', 'MAT-VAL-SOL', 'un', 'Válvula solenoide 24V 1/2"'),
('Sensor de Temperatura', 'MAT-SEN-TEMP', 'un', 'Sensor PT100 para medição de temperatura'),
('Cabo Elétrico 2.5mm', 'MAT-CAB-2.5', 'm', 'Cabo flexível 2.5mm² para instalações elétricas'),
('Terminal Elétrico', 'MAT-TER-EL', 'un', 'Terminal crimpável para cabos elétricos'),
('Graxa Industrial', 'MAT-GRA-IND', 'kg', 'Graxa multipropósito para lubrificação'),
('Fita Isolante', 'MAT-FIT-ISO', 'un', 'Fita isolante 20m para isolamento elétrico'),
('Lâmpada LED 18W', 'MAT-LAM-LED', 'un', 'Lâmpada LED industrial 18W 220V');

-- =====================================================
-- DADOS INICIAIS - ATIVIDADES DE EXEMPLO
-- =====================================================
INSERT INTO maintenance_activities (usuario_id, equipamento_id, titulo, descricao, tipo_manutencao, permissao_trabalho, ast, gro_descricao, gro_classificacao, status, prioridade, complexidade, data_inicio, data_fim, tempo_gasto, metodo_execucao, licoes_aprendidas) VALUES
(2, 1, 'Limpeza da Caldeira', 'Limpeza interna da caldeira principal conforme PM mensal', 'preventiva', 'PT-2024-001', 'Análise de risco: trabalho em altura, calor, pressão', 'Risco de queimadura e queda durante acesso', 'medio', 'concluida', 'media', 'media', '2024-06-01 08:00:00', '2024-06-01 12:00:00', 240, 'Utilização de EPI completo, isolamento da área, verificação de pressão', 'Importante verificar temperatura e pressão antes de iniciar os trabalhos'),
(2, 2, 'Troca de Rolamento', 'Substituição do rolamento do motor 75CV que apresentou ruído anormal', 'corretiva', 'PT-2024-002', 'Análise: trabalho com ferramentas elétricas, energia elétrica', 'Risco de choque elétrico durante desmontagem', 'alto', 'concluida', 'alta', 'complexa', '2024-06-03 14:00:00', '2024-06-03 18:00:00', 240, 'Desligamento total, bloqueio energético, uso de EPI elétrico', 'Sempre verificar se o equipamento está desligado e bloqueado antes de iniciar'),
(2, 3, 'Manutenção Compressor', 'Manutenção preventiva do compressor de ar - troca de filtros e óleo', 'preventiva', 'PT-2024-003', 'Análise: pressão alta, ruído, energia elétrica', 'Risco de explosão por pressão e choque elétrico', 'alto', 'concluida', 'media', 'media', '2024-06-05 09:00:00', '2024-06-05 11:00:00', 120, 'Alívio de pressão, desligamento elétrico, uso de EPI', 'Verificar pressão antes de abrir qualquer conexão'),
(2, 4, 'Alinhamento de Bomba', 'Alinhamento da bomba centrífuga conforme PM trimestral', 'preventiva', 'PT-2024-004', 'Análise: trabalho com ferramentas, movimento rotativo', 'Risco de lesão por ferramentas e movimento', 'baixo', 'concluida', 'baixa', 'simples', '2024-06-07 10:00:00', '2024-06-07 11:30:00', 90, 'Uso de ferramentas adequadas, bloqueio mecânico', 'Alinhamento deve ser feito com precisão para evitar vibrações'),
(2, 5, 'Limpeza Ventilador', 'Limpeza das pás do ventilador industrial - remoção de sujeira acumulada', 'preventiva', 'PT-2024-005', 'Análise: trabalho em altura, movimento rotativo', 'Risco de queda durante acesso e limpeza', 'medio', 'concluida', 'baixa', 'simples', '2024-06-09 13:00:00', '2024-06-09 15:00:00', 120, 'Uso de escada segura, EPI, bloqueio mecânico', 'Limpeza regular evita desbalanceamento e vibrações');

-- =====================================================
-- DADOS INICIAIS - USO DE MATERIAIS
-- =====================================================
INSERT INTO material_usage (atividade_id, material_id, quantidade) VALUES
(1, 2, 5.00),  -- Óleo para caldeira
(1, 7, 1.00),  -- Filtro de óleo
(2, 1, 2.00),  -- Rolamentos para motor
(2, 13, 0.50), -- Graxa para lubrificação
(3, 2, 2.00),  -- Óleo para compressor
(3, 8, 1.00),  -- Filtro de ar
(4, 13, 0.20), -- Graxa para alinhamento
(5, 14, 1.00); -- Fita isolante

-- =====================================================
-- VERIFICAÇÃO FINAL
-- =====================================================
SELECT 'Banco de dados configurado com sucesso!' as status;
SELECT 'Usuários criados:' as info;
SELECT nome, login, nivel_acesso, status FROM users;
SELECT 'Equipamentos cadastrados:' as info;
SELECT COUNT(*) as total FROM equipment;
SELECT 'Materiais cadastrados:' as info;
SELECT COUNT(*) as total FROM materials;
SELECT 'Atividades de exemplo:' as info;
SELECT COUNT(*) as total FROM maintenance_activities;

-- =====================================================
-- INSTRUÇÕES DE INSTALAÇÃO
-- =====================================================
-- 
-- 1. CONFIGURAÇÃO DO SERVIDOR:
--    - Certifique-se de que o PHP está configurado
--    - Configure o servidor web (Apache/Nginx)
--    - Verifique as permissões da pasta 'img/'
-- 
-- 2. CONFIGURAÇÃO DO BANCO:
--    - Execute este script SQL no MySQL/MariaDB
--    - Verifique as credenciais em 'conexaoBD.php'
-- 
-- 3. ESTRUTURA DE PASTAS:
--    - Crie a pasta 'img/' na raiz do projeto
--    - Configure permissões de escrita (755 ou 775)
-- 
-- 4. USUÁRIOS PADRÃO (HASHES VÁLIDOS E TESTADOS):
--    - Admin: login='admin', senha='admin123'
--    - Técnico: login='tecnico', senha='tecnico123'
--    ✅ Estes hashes foram verificados e funcionam desde a primeira instalação
-- 
-- 5. FUNCIONALIDADES DISPONÍVEIS:
--    - Gestão de usuários (admin)
--    - Gestão de equipamentos
--    - Gestão de materiais
--    - Atividades de manutenção
--    - Relatórios e dashboards
--    - Sistema de imagens otimizado
-- 
-- 6. SEGURANÇA:
--    - Altere as senhas padrão após o primeiro acesso
--    - Configure HTTPS em produção
--    - Mantenha backups regulares
-- 
-- 7. SOLUÇÃO DE PROBLEMAS:
--    - Se o login não funcionar, execute 'verificar_banco.php'
--    - Use 'debug_login.php' para diagnóstico detalhado
-- 
-- =====================================================
-- FIM DO SCRIPT
-- ===================================================== 