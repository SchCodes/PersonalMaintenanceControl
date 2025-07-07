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

// Buscar dados da atividade com JOIN para equipamento e usuário
$sql = "SELECT ma.*, e.nome as equipamento, e.codigo as equipamento_codigo, 
               u.nome as tecnico, u.email as tecnico_email
        FROM maintenance_activities ma
        LEFT JOIN equipment e ON ma.equipamento_id = e.id
        LEFT JOIN users u ON ma.usuario_id = u.id
        WHERE ma.id = ?";

$stmt = executarQuery($sql, [$atividade_id]);
$atividade = $stmt->fetch();

if (!$atividade) {
    header('Location: listarAtividades.php?msg=atividade_nao_encontrada');
    exit;
}

// Verificar se o usuário tem permissão para ver esta atividade
if (!$is_admin && $atividade['usuario_id'] != $usuario_id) {
    header('Location: listarAtividades.php?msg=sem_permissao');
    exit;
}

// Buscar materiais utilizados (se houver)
$sql_materiais = "SELECT m.nome, m.codigo, mu.quantidade
                  FROM material_usage mu
                  LEFT JOIN materials m ON mu.material_id = m.id
                  WHERE mu.atividade_id = ?";
$materiais = executarQuery($sql_materiais, [$atividade_id])->fetchAll();

// Processar atualização de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_status'])) {
    $novo_status = $_POST['novo_status'];
    
    // Se está marcando como concluída, definir data de fim
    if ($novo_status === 'concluida') {
        $data_fim_real = date('Y-m-d H:i:s');
    } else {
        // Se está reabrindo uma atividade concluída, limpar a data de fim
        $data_fim_real = null;
    }
    
    $sql_update = "UPDATE maintenance_activities SET status = ?, data_fim = ? WHERE id = ?";
    $stmt = executarQuery($sql_update, [$novo_status, $data_fim_real, $atividade_id]);
    
    if ($stmt->rowCount() > 0) {
        // Recarregar dados da atividade após atualização
        $stmt = executarQuery($sql, [$atividade_id]);
        $atividade = $stmt->fetch();
        $mensagem_sucesso = 'Status atualizado com sucesso!';
    } else {
        $mensagem_erro = 'Erro ao atualizar status!';
    }
}
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list"></i> Detalhes da Atividade
                </h3>
                <div class="card-tools">
                    <a href="listarAtividades.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <?php if ($is_admin || $atividade['usuario_id'] == $usuario_id): ?>
                    <a href="editarAtividade.php?id=<?php echo $atividade_id; ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($mensagem_sucesso)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $mensagem_sucesso; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($mensagem_erro)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $mensagem_erro; ?>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Informações Gerais</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Título:</strong></td>
                                <td><?php echo htmlspecialchars($atividade['titulo']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Equipamento:</strong></td>
                                <td><?php echo htmlspecialchars($atividade['equipamento']) . ' (' . htmlspecialchars($atividade['equipamento_codigo']) . ')'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tipo:</strong></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $atividade['tipo_manutencao'] === 'preventiva' ? 'success' : 
                                            ($atividade['tipo_manutencao'] === 'corretiva' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($atividade['tipo_manutencao']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Prioridade:</strong></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $atividade['prioridade'] === 'alta' ? 'danger' : 
                                            ($atividade['prioridade'] === 'media' ? 'warning' : 'success'); 
                                    ?>">
                                        <?php echo ucfirst($atividade['prioridade']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $atividade['status'] === 'concluida' ? 'success' : 
                                            ($atividade['status'] === 'pendente' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($atividade['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Datas</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Início:</strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($atividade['data_inicio'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Prevista:</strong></td>
                                <td><?php echo $atividade['data_fim'] ? date('d/m/Y H:i', strtotime($atividade['data_fim'])) : 'Não definida'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Conclusão:</strong></td>
                                <td><?php echo ($atividade['status'] === 'concluida' && $atividade['data_fim']) ? date('d/m/Y H:i', strtotime($atividade['data_fim'])) : 'Não concluída'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Registro:</strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($atividade['data_registro'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Responsável</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nome:</strong></td>
                                <td><?php echo htmlspecialchars($atividade['tecnico']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>E-mail:</strong></td>
                                <td><?php echo htmlspecialchars($atividade['tecnico_email']); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Atualizar Status</h5>
                        <?php if ($atividade['status'] !== 'concluida' || $is_admin): ?>
                        <form method="POST" class="mt-2">
                            <div class="form-group">
                                <select name="novo_status" class="form-control">
                                    <option value="pendente" <?php echo $atividade['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="em_andamento" <?php echo $atividade['status'] === 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                    <option value="concluida" <?php echo $atividade['status'] === 'concluida' ? 'selected' : ''; ?>>Concluída</option>
                                    <?php if ($is_admin && $atividade['status'] === 'concluida'): ?>
                                    <option value="pendente">Reabrir (Pendente)</option>
                                    <option value="em_andamento">Reabrir (Em Andamento)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save"></i> Atualizar Status
                            </button>
                        </form>
                        <?php else: ?>
                        <p class="text-muted">Atividade já concluída.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($atividade['descricao']): ?>
                <hr>
                <h5>Descrição</h5>
                <p><?php echo nl2br(htmlspecialchars($atividade['descricao'])); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($atividade['metodo_execucao'])): ?>
                <hr>
                <h5>Método de Execução</h5>
                <p><?php echo nl2br(htmlspecialchars($atividade['metodo_execucao'])); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($atividade['licoes_aprendidas'])): ?>
                <hr>
                <h5>Lições Aprendidas</h5>
                <p><?php echo nl2br(htmlspecialchars($atividade['licoes_aprendidas'])); ?></p>
                <?php endif; ?>
                
                <!-- Seção de Fotos -->
                <hr>
                <h5>Fotos da Atividade</h5>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Antes</h6>
                        <?php if (!empty($atividade['imagem_antes']) && file_exists($atividade['imagem_antes'])): ?>
                            <img src="<?php echo htmlspecialchars($atividade['imagem_antes']); ?>" 
                                 class="img-fluid" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 4px;" alt="Foto antes">
                        <?php else: ?>
                            <p class="text-muted">Nenhuma foto disponível</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6>Depois</h6>
                        <?php if (!empty($atividade['imagem_depois']) && file_exists($atividade['imagem_depois'])): ?>
                            <img src="<?php echo htmlspecialchars($atividade['imagem_depois']); ?>" 
                                 class="img-fluid" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 4px;" alt="Foto depois">
                        <?php else: ?>
                            <p class="text-muted">Nenhuma foto disponível</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Materiais Utilizados -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-boxes"></i> Materiais Utilizados
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($materiais)): ?>
                    <p class="text-muted">Nenhum material registrado.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Qtd</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($materiais as $material): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($material['nome']) . ' (' . htmlspecialchars($material['codigo']) . ')'; ?></td>
                                    <td><?php echo htmlspecialchars($material['quantidade']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 