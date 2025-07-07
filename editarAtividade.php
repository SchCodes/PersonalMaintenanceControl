<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

if (!isset($_GET['id'])) {
    header('Location: listarAtividades.php');
    exit;
}

$atividade_id = (int)$_GET['id'];

// Buscar dados da atividade
$sql = "SELECT * FROM maintenance_activities WHERE id = ?";
$stmt = executarQuery($sql, [$atividade_id]);
$atividade = $stmt->fetch();

if (!$atividade) {
    header('Location: listarAtividades.php?msg=atividade_nao_encontrada');
    exit;
}

// Verificar permissão
if (!$is_admin && $atividade['usuario_id'] != $usuario_id) {
    header('Location: listarAtividades.php?msg=sem_permissao');
    exit;
}

// Buscar equipamentos
$sql_equipamentos = "SELECT id, nome, codigo FROM equipment ORDER BY nome";
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
    $data_fim = $_POST['data_fim_prevista'] ? $_POST['data_fim_prevista'] : $data_inicio;
    $responsavel_id = $is_admin ? $_POST['responsavel_id'] : $atividade['usuario_id'];
    $metodo_execucao = sanitizar($_POST['metodo_execucao'] ?? '');
    $licoes_aprendidas = sanitizar($_POST['licoes_aprendidas'] ?? '');
    
    // Processar imagens
    $imagem_antes = null;
    $imagem_depois = null;
    $excluir_imagem_antes = isset($_POST['excluir_imagem_antes']);
    $excluir_imagem_depois = isset($_POST['excluir_imagem_depois']);
    
    if (isset($_FILES['imagem_antes']) && $_FILES['imagem_antes']['error'] === UPLOAD_ERR_OK) {
        $validacao = validarImagem($_FILES['imagem_antes']);
        if ($validacao === true) {
            $imagem_antes = salvarImagem($_FILES['imagem_antes'], 'img/atividades/');
        } else {
            $mensagem = '<div class="alert alert-danger">Erro na foto antes: ' . $validacao . '</div>';
        }
    }
    
    if (isset($_FILES['imagem_depois']) && $_FILES['imagem_depois']['error'] === UPLOAD_ERR_OK) {
        error_log("DEBUG: Upload de imagem_depois detectado");
        $validacao = validarImagem($_FILES['imagem_depois']);
        if ($validacao === true) {
            $imagem_depois = salvarImagem($_FILES['imagem_depois'], 'img/atividades/');
            error_log("DEBUG: imagem_depois salva em: " . $imagem_depois);
        } else {
            error_log("DEBUG: Erro na validação imagem_depois: " . $validacao);
            $mensagem = '<div class="alert alert-danger">Erro na foto depois: ' . $validacao . '</div>';
        }
    } else {
        error_log("DEBUG: imagem_depois não enviada ou com erro: " . (isset($_FILES['imagem_depois']) ? $_FILES['imagem_depois']['error'] : 'não definida'));
    }
    
    // Validações
    if (empty($titulo) || empty($equipamento_id) || empty($data_inicio)) {
        $mensagem = '<div class="alert alert-danger">Preencha todos os campos obrigatórios!</div>';
    } else {
        try {
            // Construir query dinamicamente baseada nas imagens
            $campos = [
                'titulo = ?', 'descricao = ?', 'equipamento_id = ?', 'tipo_manutencao = ?', 
                'prioridade = ?', 'data_inicio = ?', 'data_fim = ?', 'usuario_id = ?', 
                'metodo_execucao = ?', 'licoes_aprendidas = ?'
            ];
            $valores = [
                $titulo, $descricao, $equipamento_id, $tipo_manutencao, $prioridade,
                $data_inicio, $data_fim, $responsavel_id, $metodo_execucao, $licoes_aprendidas
            ];
            
            // Tratar imagem antes
            if ($excluir_imagem_antes) {
                $campos[] = 'imagem_antes = NULL';
            } elseif ($imagem_antes) {
                $campos[] = 'imagem_antes = ?';
                $valores[] = $imagem_antes;
            }
            
            // Tratar imagem depois
            if ($excluir_imagem_depois) {
                $campos[] = 'imagem_depois = NULL';
                error_log("DEBUG: Excluindo imagem_depois");
            } elseif ($imagem_depois) {
                $campos[] = 'imagem_depois = ?';
                $valores[] = $imagem_depois;
                error_log("DEBUG: Atualizando imagem_depois para: " . $imagem_depois);
            } else {
                error_log("DEBUG: imagem_depois não foi alterada");
            }
            
            $valores[] = $atividade_id; // ID para WHERE
            
            $sql = "UPDATE maintenance_activities SET " . implode(', ', $campos) . " WHERE id = ?";
            error_log("DEBUG: SQL gerado: " . $sql);
            error_log("DEBUG: Valores: " . print_r($valores, true));
            
            $stmt = executarQuery($sql, $valores);
            
            if ($stmt->rowCount() > 0) {
                $mensagem = '<div class="alert alert-success">Atividade atualizada com sucesso!</div>';
                error_log("DEBUG: Atualização bem-sucedida. Linhas afetadas: " . $stmt->rowCount());
                
                // Recarregar dados da atividade
                $stmt = executarQuery("SELECT * FROM maintenance_activities WHERE id = ?", [$atividade_id]);
                $atividade = $stmt->fetch();
            } else {
                $mensagem = '<div class="alert alert-info">Nenhuma alteração foi feita.</div>';
                error_log("DEBUG: Nenhuma linha foi atualizada. Possível problema na query ou dados idênticos.");
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
                    <i class="fas fa-edit"></i> Editar Atividade de Manutenção
                </h3>
                <div class="card-tools">
                    <a href="visualizarAtividade.php?id=<?php echo $atividade_id; ?>" class="btn btn-secondary btn-sm">
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
                                       value="<?php echo htmlspecialchars($atividade['titulo']); ?>" 
                                       required maxlength="200">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo_manutencao">Tipo de Manutenção *</label>
                                <select class="form-control" id="tipo_manutencao" name="tipo_manutencao" required>
                                    <option value="">Selecione...</option>
                                    <option value="preventiva" <?php echo $atividade['tipo_manutencao'] === 'preventiva' ? 'selected' : ''; ?>>Preventiva</option>
                                    <option value="corretiva" <?php echo $atividade['tipo_manutencao'] === 'corretiva' ? 'selected' : ''; ?>>Corretiva</option>
                                    <option value="preditiva" <?php echo $atividade['tipo_manutencao'] === 'preditiva' ? 'selected' : ''; ?>>Preditiva</option>
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
                                            <?php echo $atividade['equipamento_id'] == $equip['id'] ? 'selected' : ''; ?>>
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
                                            <?php echo $atividade['usuario_id'] == $tecnico['id'] ? 'selected' : ''; ?>>
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
                                       value="<?php echo date('Y-m-d\TH:i', strtotime($atividade['data_inicio'])); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_fim_prevista">Data de Conclusão Prevista</label>
                                <input type="datetime-local" class="form-control" id="data_fim_prevista" name="data_fim_prevista" 
                                       value="<?php echo $atividade['data_fim'] ? date('Y-m-d\TH:i', strtotime($atividade['data_fim'])) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="prioridade">Prioridade</label>
                                <select class="form-control" id="prioridade" name="prioridade">
                                    <option value="baixa" <?php echo $atividade['prioridade'] === 'baixa' ? 'selected' : ''; ?>>Baixa</option>
                                    <option value="media" <?php echo $atividade['prioridade'] === 'media' ? 'selected' : ''; ?>>Média</option>
                                    <option value="alta" <?php echo $atividade['prioridade'] === 'alta' ? 'selected' : ''; ?>>Alta</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status" disabled>
                                    <option value="pendente" <?php echo $atividade['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="em_andamento" <?php echo $atividade['status'] === 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                    <option value="concluida" <?php echo $atividade['status'] === 'concluida' ? 'selected' : ''; ?>>Concluída</option>
                                </select>
                                <small class="form-text text-muted">O status deve ser alterado na visualização da atividade.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descricao">Descrição da Atividade</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4" 
                                  placeholder="Descreva detalhadamente a atividade a ser realizada..."><?php echo htmlspecialchars($atividade['descricao']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="metodo_execucao">Método de Execução</label>
                        <textarea class="form-control" id="metodo_execucao" name="metodo_execucao" rows="3" 
                                  placeholder="Descreva o método de execução..."><?php echo htmlspecialchars($atividade['metodo_execucao'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="licoes_aprendidas">Lições Aprendidas</label>
                        <textarea class="form-control" id="licoes_aprendidas" name="licoes_aprendidas" rows="3" 
                                  placeholder="Lições aprendidas durante a execução..."><?php echo htmlspecialchars($atividade['licoes_aprendidas'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Seção de Fotos -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="imagem_antes">Foto Antes</label>
                                <input type="file" class="form-control" id="imagem_antes" name="imagem_antes" accept="image/jpeg,image/jpg,image/png">
                                <small class="form-text text-muted">Formatos aceitos: JPG, JPEG, PNG. Máximo 10MB.</small>
                                <?php if (!empty($atividade['imagem_antes']) && file_exists($atividade['imagem_antes'])): ?>
                                    <div class="mt-2">
                                        <small class="text-info">Foto atual disponível</small>
                                        <br>
                                        <img src="<?php echo htmlspecialchars($atividade['imagem_antes']); ?>" 
                                             class="img-fluid mt-1" style="max-width: 200px; border: 1px solid #ddd; border-radius: 4px;" alt="Foto antes">
                                        <br>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="excluir_imagem_antes" name="excluir_imagem_antes">
                                            <label class="form-check-label" for="excluir_imagem_antes">
                                                Excluir foto atual
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="imagem_depois">Foto Depois</label>
                                <input type="file" class="form-control" id="imagem_depois" name="imagem_depois" accept="image/jpeg,image/jpg,image/png">
                                <small class="form-text text-muted">Formatos aceitos: JPG, JPEG, PNG. Máximo 10MB.</small>
                                <?php if (!empty($atividade['imagem_depois']) && file_exists($atividade['imagem_depois'])): ?>
                                    <div class="mt-2">
                                        <small class="text-info">Foto atual disponível</small>
                                        <br>
                                        <img src="<?php echo htmlspecialchars($atividade['imagem_depois']); ?>" 
                                             class="img-fluid mt-1" style="max-width: 200px; border: 1px solid #ddd; border-radius: 4px;" alt="Foto depois">
                                        <br>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="excluir_imagem_depois" name="excluir_imagem_depois">
                                            <label class="form-check-label" for="excluir_imagem_depois">
                                                Excluir foto atual
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Atualizar Atividade
                        </button>
                        <a href="visualizarAtividade.php?id=<?php echo $atividade_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 