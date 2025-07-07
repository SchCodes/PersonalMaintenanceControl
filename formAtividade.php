<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

// Buscar equipamentos (apenas ativos)
$sql_equipamentos = "SELECT id, nome, codigo FROM equipment WHERE status = 'ativo' ORDER BY nome";
$equipamentos = executarQuery($sql_equipamentos)->fetchAll();

// Buscar materiais
$sql_materiais = "SELECT id, nome, codigo FROM materials ORDER BY nome";
$materiais = executarQuery($sql_materiais)->fetchAll();

// Buscar técnicos (apenas para admin)
$tecnicos = [];
if ($is_admin) {
    $sql_tecnicos = "SELECT id, nome FROM users WHERE nivel_acesso = 'tecnico' AND status = 1 ORDER BY nome";
    $tecnicos = executarQuery($sql_tecnicos)->fetchAll();
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = sanitizar($_POST['titulo']);
    $descricao = sanitizar($_POST['descricao']);
    $equipamento_id = $_POST['equipamento_id'];
    $tipo_manutencao = $_POST['tipo_manutencao'];
    $prioridade = $_POST['prioridade'];
    $complexidade = $_POST['complexidade'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim_prevista'] ? $_POST['data_fim_prevista'] : $data_inicio;
    $responsavel_id = $is_admin ? $_POST['responsavel_id'] : $usuario_id;
    $materiais_utilizados = isset($_POST['materiais']) ? $_POST['materiais'] : [];
    
    // Campos de segurança
    $permissao_trabalho = sanitizar($_POST['permissao_trabalho']);
    $ast = sanitizar($_POST['ast']);
    $gro_descricao = sanitizar($_POST['gro_descricao']);
    $gro_classificacao = $_POST['gro_classificacao'];
    $metodo_execucao = sanitizar($_POST['metodo_execucao']);
    $licoes_aprendidas = sanitizar($_POST['licoes_aprendidas']);
    
    // Processar imagens
    $imagem_antes = null;
    $imagem_depois = null;
    
    // Debug: Verificar dados FILES
    error_log("DEBUG: Dados FILES recebidos: " . print_r($_FILES, true));
    
    if (isset($_FILES['imagem_antes']) && $_FILES['imagem_antes']['error'] === UPLOAD_ERR_OK) {
        error_log("DEBUG: Processando imagem_antes");
        $validacao = validarImagem($_FILES['imagem_antes']);
        if ($validacao === true) {
            $imagem_antes = salvarImagem($_FILES['imagem_antes'], 'img/atividades/');
            error_log("DEBUG: imagem_antes salva em: " . $imagem_antes);
        } else {
            error_log("DEBUG: Erro na validação imagem_antes: " . $validacao);
            $mensagem = '<div class="alert alert-danger">Erro na foto antes: ' . $validacao . '</div>';
        }
    } else {
        error_log("DEBUG: imagem_antes não enviada ou com erro: " . (isset($_FILES['imagem_antes']) ? $_FILES['imagem_antes']['error'] : 'não definida'));
    }
    
    if (isset($_FILES['imagem_depois']) && $_FILES['imagem_depois']['error'] === UPLOAD_ERR_OK) {
        error_log("DEBUG: Processando imagem_depois na criação");
        $validacao = validarImagem($_FILES['imagem_depois']);
        if ($validacao === true) {
            $imagem_depois = salvarImagem($_FILES['imagem_depois'], 'img/atividades/');
            error_log("DEBUG: imagem_depois salva em: " . $imagem_depois);
        } else {
            error_log("DEBUG: Erro na validação imagem_depois: " . $validacao);
            $mensagem = '<div class="alert alert-danger">Erro na foto depois: ' . $validacao . '</div>';
        }
    } else {
        error_log("DEBUG: imagem_depois não enviada ou com erro na criação: " . (isset($_FILES['imagem_depois']) ? $_FILES['imagem_depois']['error'] : 'não definida'));
    }
    
    // Validações
    if (empty($titulo) || empty($equipamento_id) || empty($data_inicio)) {
        $mensagem = '<div class="alert alert-danger">Preencha todos os campos obrigatórios!</div>';
    } else {
        try {
            // Inserir atividade
            $sql = "INSERT INTO maintenance_activities (
                titulo, descricao, equipamento_id, tipo_manutencao, prioridade, complexidade,
                data_inicio, data_fim, usuario_id, status, permissao_trabalho, ast, 
                gro_descricao, gro_classificacao, metodo_execucao, licoes_aprendidas,
                imagem_antes, imagem_depois, data_registro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente', ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            error_log("DEBUG: Valores para inserção - imagem_antes: " . $imagem_antes . ", imagem_depois: " . $imagem_depois);
            
            $stmt = executarQuery($sql, [
                $titulo, $descricao, $equipamento_id, $tipo_manutencao, $prioridade, $complexidade,
                $data_inicio, $data_fim, $responsavel_id, $permissao_trabalho, $ast, 
                $gro_descricao, $gro_classificacao, $metodo_execucao, $licoes_aprendidas,
                $imagem_antes, $imagem_depois
            ]);
            
            if ($stmt->rowCount() > 0) {
                $atividade_id = $GLOBALS['pdo']->lastInsertId();
                
                // Inserir materiais utilizados
                if (!empty($materiais_utilizados)) {
                    foreach ($materiais_utilizados as $material_id) {
                        $sql_material = "INSERT INTO material_usage (atividade_id, material_id, quantidade) VALUES (?, ?, 1)";
                        executarQuery($sql_material, [$atividade_id, $material_id]);
                    }
                }
                
                $mensagem = '<div class="alert alert-success">Atividade criada com sucesso!</div>';
                
                // Limpar formulário
                $_POST = array();
            } else {
                $mensagem = '<div class="alert alert-danger">Erro ao criar atividade!</div>';
            }
        } catch (Exception $e) {
            $mensagem = '<div class="alert alert-danger">Erro: ' . $e->getMessage() . '</div>';
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus"></i> Nova Atividade de Manutenção
                </h3>
                <div class="card-tools">
                    <a href="listarAtividades.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <?php echo $mensagem; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <!-- Informações Básicas -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="titulo">Título da Atividade *</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" 
                                       value="<?php echo isset($_POST['titulo']) ? $_POST['titulo'] : ''; ?>" 
                                       required maxlength="200">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo_manutencao">Tipo de Manutenção *</label>
                                <select class="form-control" id="tipo_manutencao" name="tipo_manutencao" required>
                                    <option value="">Selecione...</option>
                                    <option value="preventiva" <?php echo (isset($_POST['tipo_manutencao']) && $_POST['tipo_manutencao'] === 'preventiva') ? 'selected' : ''; ?>>Preventiva</option>
                                    <option value="corretiva" <?php echo (isset($_POST['tipo_manutencao']) && $_POST['tipo_manutencao'] === 'corretiva') ? 'selected' : ''; ?>>Corretiva</option>
                                    <option value="preditiva" <?php echo (isset($_POST['tipo_manutencao']) && $_POST['tipo_manutencao'] === 'preditiva') ? 'selected' : ''; ?>>Preditiva</option>
                                    <option value="outra" <?php echo (isset($_POST['tipo_manutencao']) && $_POST['tipo_manutencao'] === 'outra') ? 'selected' : ''; ?>>Outra</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="equipamento_id">Equipamento *</label>
                                <select class="form-control" id="equipamento_id" name="equipamento_id" required>
                                    <option value="">Selecione o equipamento...</option>
                                    <?php foreach ($equipamentos as $equip): ?>
                                    <option value="<?php echo $equip['id']; ?>" 
                                            <?php echo (isset($_POST['equipamento_id']) && $_POST['equipamento_id'] == $equip['id']) ? 'selected' : ''; ?>>
                                        <?php echo $equip['nome'] . ' (' . $equip['codigo'] . ')'; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <?php if ($is_admin): ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="responsavel_id">Responsável</label>
                                <select class="form-control" id="responsavel_id" name="responsavel_id">
                                    <option value="">Selecione o técnico...</option>
                                    <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?php echo $tecnico['id']; ?>" 
                                            <?php echo (isset($_POST['responsavel_id']) && $_POST['responsavel_id'] == $tecnico['id']) ? 'selected' : ''; ?>>
                                        <?php echo $tecnico['nome']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="data_inicio">Data de Início *</label>
                                <input type="datetime-local" class="form-control" id="data_inicio" name="data_inicio" 
                                       value="<?php echo isset($_POST['data_inicio']) ? $_POST['data_inicio'] : date('Y-m-d\TH:i'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="data_fim_prevista">Data de Conclusão Prevista</label>
                                <input type="datetime-local" class="form-control" id="data_fim_prevista" name="data_fim_prevista" 
                                       value="<?php echo isset($_POST['data_fim_prevista']) ? $_POST['data_fim_prevista'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="prioridade">Prioridade</label>
                                <select class="form-control" id="prioridade" name="prioridade">
                                    <option value="baixa" <?php echo (isset($_POST['prioridade']) && $_POST['prioridade'] === 'baixa') ? 'selected' : ''; ?>>Baixa</option>
                                    <option value="media" <?php echo (isset($_POST['prioridade']) && $_POST['prioridade'] === 'media') ? 'selected' : ''; ?>>Média</option>
                                    <option value="alta" <?php echo (isset($_POST['prioridade']) && $_POST['prioridade'] === 'alta') ? 'selected' : ''; ?>>Alta</option>
                                    <option value="critica" <?php echo (isset($_POST['prioridade']) && $_POST['prioridade'] === 'critica') ? 'selected' : ''; ?>>Crítica</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="complexidade">Complexidade</label>
                                <select class="form-control" id="complexidade" name="complexidade">
                                    <option value="simples" <?php echo (isset($_POST['complexidade']) && $_POST['complexidade'] === 'simples') ? 'selected' : ''; ?>>Simples</option>
                                    <option value="media" <?php echo (isset($_POST['complexidade']) && $_POST['complexidade'] === 'media') ? 'selected' : ''; ?>>Média</option>
                                    <option value="complexa" <?php echo (isset($_POST['complexidade']) && $_POST['complexidade'] === 'complexa') ? 'selected' : ''; ?>>Complexa</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="materiais">Materiais Utilizados</label>
                                <select class="form-control" id="materiais" name="materiais[]" multiple>
                                    <?php foreach ($materiais as $material): ?>
                                    <option value="<?php echo $material['id']; ?>" 
                                            <?php echo (isset($_POST['materiais']) && in_array($material['id'], $_POST['materiais'])) ? 'selected' : ''; ?>>
                                        <?php echo $material['nome'] . ' (' . $material['codigo'] . ')'; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Pressione Ctrl para selecionar múltiplos materiais.</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campos de Segurança -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-shield-alt"></i> Informações de Segurança
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="permissao_trabalho">Permissão de Trabalho (PT)</label>
                                        <input type="text" class="form-control" id="permissao_trabalho" name="permissao_trabalho" 
                                               value="<?php echo isset($_POST['permissao_trabalho']) ? $_POST['permissao_trabalho'] : ''; ?>" 
                                               placeholder="Ex: PT-2024-001">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gro_classificacao">Classificação do GRO</label>
                                        <select class="form-control" id="gro_classificacao" name="gro_classificacao">
                                            <option value="">Selecione...</option>
                                            <option value="baixo" <?php echo (isset($_POST['gro_classificacao']) && $_POST['gro_classificacao'] === 'baixo') ? 'selected' : ''; ?>>Baixo</option>
                                            <option value="medio" <?php echo (isset($_POST['gro_classificacao']) && $_POST['gro_classificacao'] === 'medio') ? 'selected' : ''; ?>>Médio</option>
                                            <option value="alto" <?php echo (isset($_POST['gro_classificacao']) && $_POST['gro_classificacao'] === 'alto') ? 'selected' : ''; ?>>Alto</option>
                                            <option value="critico" <?php echo (isset($_POST['gro_classificacao']) && $_POST['gro_classificacao'] === 'critico') ? 'selected' : ''; ?>>Crítico</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="ast">Análise de Segurança do Trabalho (AST)</label>
                                <textarea class="form-control" id="ast" name="ast" rows="3" 
                                          placeholder="Descreva a análise de segurança do trabalho..."><?php echo isset($_POST['ast']) ? $_POST['ast'] : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="gro_descricao">Descrição do GRO (Gestão de Riscos Ocupacionais)</label>
                                <textarea class="form-control" id="gro_descricao" name="gro_descricao" rows="3" 
                                          placeholder="Descreva os riscos ocupacionais identificados..."><?php echo isset($_POST['gro_descricao']) ? $_POST['gro_descricao'] : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="metodo_execucao">Método de Execução</label>
                                <textarea class="form-control" id="metodo_execucao" name="metodo_execucao" rows="3" 
                                          placeholder="Descreva o método de execução da atividade..."><?php echo isset($_POST['metodo_execucao']) ? $_POST['metodo_execucao'] : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Descrições -->
                    <div class="form-group">
                        <label for="descricao">Descrição da Atividade</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4" 
                                  placeholder="Descreva detalhadamente a atividade a ser realizada..."><?php echo isset($_POST['descricao']) ? $_POST['descricao'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="licoes_aprendidas">Lições Aprendidas</label>
                        <textarea class="form-control" id="licoes_aprendidas" name="licoes_aprendidas" rows="3" 
                                  placeholder="Registre lições aprendidas durante a atividade..."><?php echo isset($_POST['licoes_aprendidas']) ? $_POST['licoes_aprendidas'] : ''; ?></textarea>
                    </div>
                    
                    <!-- Seção de Fotos -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-camera"></i> Documentação Fotográfica
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="imagem_antes">Foto Antes</label>
                                        <input type="file" class="form-control" id="imagem_antes" name="imagem_antes" accept="image/jpeg,image/jpg,image/png">
                                        <small class="form-text text-muted">Formatos aceitos: JPG, JPEG, PNG. Máximo 10MB.</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="imagem_depois">Foto Depois</label>
                                        <input type="file" class="form-control" id="imagem_depois" name="imagem_depois" accept="image/jpeg,image/jpg,image/png">
                                        <small class="form-text text-muted">Formatos aceitos: JPG, JPEG, PNG. Máximo 10MB.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Criar Atividade
                        </button>
                        <a href="listarAtividades.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Configurar data de início padrão
    if (!$('#data_inicio').val()) {
        $('#data_inicio').val(new Date().toISOString().slice(0, 16));
    }
    
    // Configurar data de fim prevista (7 dias depois)
    if (!$('#data_fim_prevista').val()) {
        const dataFim = new Date();
        dataFim.setDate(dataFim.getDate() + 7);
        $('#data_fim_prevista').val(dataFim.toISOString().slice(0, 16));
    }
});
</script>

<?php require_once 'footer.php'; ?> 