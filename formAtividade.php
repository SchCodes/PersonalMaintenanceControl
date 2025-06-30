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
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim_prevista'] ? $_POST['data_fim_prevista'] : $data_inicio; // Se não informada, usa a data de início
    $responsavel_id = $is_admin ? $_POST['responsavel_id'] : $usuario_id;
    $materiais_utilizados = isset($_POST['materiais']) ? $_POST['materiais'] : [];
    $observacoes = sanitizar($_POST['observacoes']);
    
    // Processar imagens
    $imagem_antes_blob = null;
    $imagem_depois_blob = null;
    
    if (isset($_FILES['imagem_antes']) && $_FILES['imagem_antes']['error'] === UPLOAD_ERR_OK) {
        $validacao = validarImagem($_FILES['imagem_antes']);
        if ($validacao === true) {
            $imagem_antes_blob = imagemParaBlob($_FILES['imagem_antes']);
        } else {
            $mensagem = '<div class="alert alert-danger">Erro na foto antes: ' . $validacao . '</div>';
        }
    }
    
    if (isset($_FILES['imagem_depois']) && $_FILES['imagem_depois']['error'] === UPLOAD_ERR_OK) {
        $validacao = validarImagem($_FILES['imagem_depois']);
        if ($validacao === true) {
            $imagem_depois_blob = imagemParaBlob($_FILES['imagem_depois']);
        } else {
            $mensagem = '<div class="alert alert-danger">Erro na foto depois: ' . $validacao . '</div>';
        }
    }
    
    // Validações
    if (empty($titulo) || empty($equipamento_id) || empty($data_inicio)) {
        $mensagem = '<div class="alert alert-danger">Preencha todos os campos obrigatórios!</div>';
    } else {
        try {
            // Inserir atividade
            $sql = "INSERT INTO maintenance_activities (
                titulo, descricao, equipamento_id, tipo_manutencao, prioridade, 
                data_inicio, data_fim, usuario_id, status, data_registro, imagem_antes, imagem_depois
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendente', NOW(), ?, ?)";
            
            $stmt = executarQuery($sql, [
                $titulo, $descricao, $equipamento_id, $tipo_manutencao, $prioridade,
                $data_inicio, $data_fim, $responsavel_id, $imagem_antes_blob, $imagem_depois_blob
            ]);
            
            if ($stmt->rowCount() > 0) {
                $atividade_id = $GLOBALS['pdo']->lastInsertId();
                
                // Inserir materiais utilizados (temporariamente desabilitado)
                // if (!empty($materiais_utilizados)) {
                //     foreach ($materiais_utilizados as $material_id) {
                //         $sql_material = "INSERT INTO material_usage (atividade_id, material_id, quantidade) VALUES (?, ?, 1)";
                //         executarQuery($sql_material, [$atividade_id, $material_id]);
                //     }
                // }
                
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_inicio">Data de Início *</label>
                                <input type="datetime-local" class="form-control" id="data_inicio" name="data_inicio" 
                                       value="<?php echo isset($_POST['data_inicio']) ? $_POST['data_inicio'] : date('Y-m-d\TH:i'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_fim_prevista">Data de Conclusão Prevista</label>
                                <input type="datetime-local" class="form-control" id="data_fim_prevista" name="data_fim_prevista" 
                                       value="<?php echo isset($_POST['data_fim_prevista']) ? $_POST['data_fim_prevista'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="prioridade">Prioridade</label>
                                <select class="form-control" id="prioridade" name="prioridade">
                                    <option value="baixa" <?php echo (isset($_POST['prioridade']) && $_POST['prioridade'] === 'baixa') ? 'selected' : ''; ?>>Baixa</option>
                                    <option value="media" <?php echo (isset($_POST['prioridade']) && $_POST['prioridade'] === 'media') ? 'selected' : ''; ?>>Média</option>
                                    <option value="alta" <?php echo (isset($_POST['prioridade']) && $_POST['prioridade'] === 'alta') ? 'selected' : ''; ?>>Alta</option>
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
                    
                    <div class="form-group">
                        <label for="descricao">Descrição da Atividade</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4" 
                                  placeholder="Descreva detalhadamente a atividade a ser realizada..."><?php echo isset($_POST['descricao']) ? $_POST['descricao'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoes">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3" 
                                  placeholder="Observações adicionais..."><?php echo isset($_POST['observacoes']) ? $_POST['observacoes'] : ''; ?></textarea>
                    </div>
                    
                    <!-- Seção de Fotos -->
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
                    
                    <div class="form-group">
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