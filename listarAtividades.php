<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

// Filtros
$filtro_status = isset($_GET['status']) ? $_GET['status'] : '';
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$filtro_data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

// Construir query base
$sql = "SELECT ma.*, u.nome as tecnico, e.nome as equipamento 
        FROM maintenance_activities ma 
        JOIN users u ON ma.usuario_id = u.id 
        JOIN equipment e ON ma.equipamento_id = e.id";

$params = [];
$where_conditions = [];

// Aplicar filtros de nível de acesso
if (!$is_admin) {
    $where_conditions[] = "ma.usuario_id = ?";
    $params[] = $usuario_id;
}

// Aplicar filtros opcionais
if ($filtro_status) {
    $where_conditions[] = "ma.status = ?";
    $params[] = $filtro_status;
}

if ($filtro_tipo) {
    $where_conditions[] = "ma.tipo_manutencao = ?";
    $params[] = $filtro_tipo;
}

if ($filtro_data_inicio) {
    $where_conditions[] = "ma.data_inicio >= ?";
    $params[] = $filtro_data_inicio . ' 00:00:00';
}

if ($filtro_data_fim) {
    $where_conditions[] = "ma.data_inicio <= ?";
    $params[] = $filtro_data_fim . ' 23:59:59';
}

// Adicionar condições WHERE se houver
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY ma.data_inicio DESC";

$atividades = executarQuery($sql, $params)->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list"></i> Atividades de Manutenção
                </h3>
                <div class="card-tools">
                    <a href="formAtividade.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nova Atividade
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php 
                        switch($_GET['msg']) {
                            case 'atividade_excluida': echo 'Atividade excluída com sucesso!'; break;
                            case 'atividade_atualizada': echo 'Atividade atualizada com sucesso!'; break;
                            case 'atividade_cadastrada': echo 'Atividade cadastrada com sucesso!'; break;
                            case 'atividade_nao_encontrada': echo 'Atividade não encontrada!'; break;
                            case 'id_nao_fornecido': echo 'ID da atividade não fornecido!'; break;
                            case 'erro_excluir_atividade': echo 'Erro ao excluir atividade!'; break;
                            case 'erro_banco': echo 'Erro no banco de dados!'; break;
                            default: echo $_GET['msg']; break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Filtros -->
                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-md-2">
                            <label>Status:</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="pendente" <?php echo $filtro_status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                <option value="em_andamento" <?php echo $filtro_status === 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                <option value="concluida" <?php echo $filtro_status === 'concluida' ? 'selected' : ''; ?>>Concluída</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Tipo:</label>
                            <select name="tipo" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="preventiva" <?php echo $filtro_tipo === 'preventiva' ? 'selected' : ''; ?>>Preventiva</option>
                                <option value="corretiva" <?php echo $filtro_tipo === 'corretiva' ? 'selected' : ''; ?>>Corretiva</option>
                                <option value="preditiva" <?php echo $filtro_tipo === 'preditiva' ? 'selected' : ''; ?>>Preditiva</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Data Início:</label>
                            <input type="date" name="data_inicio" value="<?php echo $filtro_data_inicio; ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label>Data Fim:</label>
                            <input type="date" name="data_fim" value="<?php echo $filtro_data_fim; ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label><br>
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="listarAtividades.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Tabela de Atividades -->
                <?php if (empty($atividades)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nenhuma atividade encontrada.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tabelaAtividades">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <?php if ($is_admin): ?>
                                    <th>Técnico</th>
                                    <?php endif; ?>
                                    <th>Equipamento</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Prioridade</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($atividades as $atividade): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($atividade['data_inicio'])); ?></td>
                                    <?php if ($is_admin): ?>
                                    <td><?php echo $atividade['tecnico']; ?></td>
                                    <?php endif; ?>
                                    <td><?php echo $atividade['equipamento']; ?></td>
                                    <td><?php echo $atividade['titulo']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $atividade['tipo_manutencao'] === 'preventiva' ? 'success' : 
                                                ($atividade['tipo_manutencao'] === 'corretiva' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($atividade['tipo_manutencao']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $atividade['status'] === 'concluida' ? 'success' : 
                                                ($atividade['status'] === 'pendente' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo ucfirst($atividade['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $atividade['prioridade'] === 'alta' ? 'danger' : 
                                                ($atividade['prioridade'] === 'media' ? 'warning' : 'success'); 
                                        ?>">
                                            <?php echo ucfirst($atividade['prioridade']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="visualizarAtividade.php?id=<?php echo $atividade['id']; ?>" 
                                               class="btn btn-sm btn-info" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($is_admin || $atividade['usuario_id'] == $usuario_id): ?>
                                            <a href="editarAtividade.php?id=<?php echo $atividade['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($is_admin): ?>
                                            <a href="excluirAtividade.php?id=<?php echo $atividade['id']; ?>" 
                                               class="btn btn-sm btn-danger" title="Excluir"
                                               onclick="return confirm('Tem certeza que deseja excluir esta atividade?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
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

<script>
$(document).ready(function() {
    $('#tabelaAtividades').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.0/i18n/pt-BR.json"
        },
        "pageLength": 25,
        "order": [[0, "desc"]]
    });
});
</script>

<?php require_once 'footer.php'; ?> 