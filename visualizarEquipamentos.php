<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

// Se foi passado um ID específico, mostrar detalhes do equipamento
if (isset($_GET['id'])) {
    $equipamento_id = (int)$_GET['id'];
    
    // Buscar dados do equipamento
    $sql = "SELECT * FROM equipment WHERE id = ?";
    $stmt = executarQuery($sql, [$equipamento_id]);
    $equipamento = $stmt->fetch();
    
    if (!$equipamento) {
        header('Location: listarEquipamentos.php?msg=equipamento_nao_encontrado');
        exit;
    }
    ?>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i> Detalhes do Equipamento
                    </h3>
                    <div class="card-tools">
                        <a href="<?php echo $is_admin ? 'listarEquipamentos.php' : 'listarEquipamentosTecnico.php'; ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                        <?php if ($is_admin): ?>
                        <a href="formEquipamento.php?id=<?php echo $equipamento['id']; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">ID:</th>
                                    <td><?php echo $equipamento['id']; ?></td>
                                </tr>
                                <tr>
                                    <th>Nome:</th>
                                    <td><?php echo htmlspecialchars($equipamento['nome']); ?></td>
                                </tr>
                                <tr>
                                    <th>Código:</th>
                                    <td><?php echo htmlspecialchars($equipamento['codigo'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th>Tipo:</th>
                                    <td><?php echo htmlspecialchars($equipamento['tipo'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $equipamento['status'] === 'ativo' ? 'success' : 
                                                ($equipamento['status'] === 'inativo' ? 'secondary' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst($equipamento['status'] ?? 'ativo'); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Localização:</th>
                                    <td><?php echo htmlspecialchars($equipamento['localizacao'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th>Área da Planta:</th>
                                    <td><?php echo htmlspecialchars($equipamento['area_planta'] ?? 'Não informado'); ?></td>
                                </tr>
                            </table>
                            
                            <?php if (!empty($equipamento['descricao'])): ?>
                            <h5>Descrição</h5>
                            <p><?php echo nl2br(htmlspecialchars($equipamento['descricao'])); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($equipamento['imagem'])): ?>
                            <h5>Imagem</h5>
                            <?php if (file_exists($equipamento['imagem'])): ?>
                                <img src="<?php echo $equipamento['imagem']; ?>" 
                                     class="img-fluid" style="max-width: 300px;" alt="Imagem do equipamento">
                            <?php else: ?>
                                <p class="text-muted">Imagem não encontrada</p>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
} else {
    // Listagem geral de equipamentos (para técnicos)
    // Buscar equipamentos
    $sql = "SELECT * FROM equipment ORDER BY nome";
    $equipamentos = executarQuery($sql)->fetchAll();
    ?>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Equipamentos
                    </h3>
                    <div class="card-tools">
                        <?php if ($is_admin): ?>
                        <a href="formEquipamento.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Novo Equipamento
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (empty($equipamentos)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhum equipamento cadastrado.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tabelaEquipamentos">
                                <thead>
                                    <tr>
                                        <th>Imagem</th>
                                        <th>Código</th>
                                        <th>Nome</th>
                                        <th>Tipo</th>
                                        <th>Status</th>
                                        <th>Localização</th>
                                        <th>Área</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipamentos as $equip): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($equip['imagem'])): ?>
                                                <?php if (file_exists($equip['imagem'])): ?>
                                                    <img src="<?php echo $equip['imagem']; ?>" 
                                                         class="img-thumbnail" style="max-width: 50px; height: auto;" 
                                                         alt="Imagem do equipamento" title="<?php echo htmlspecialchars($equip['nome']); ?>">
                                                <?php else: ?>
                                                    <span class="text-muted">Imagem não encontrada</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <svg width="50" height="50" viewBox="0 0 24 24" fill="currentColor" class="text-muted">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                                </svg>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($equip['codigo'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($equip['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($equip['tipo'] ?? ''); ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $equip['status'] === 'ativo' ? 'success' : 
                                                    ($equip['status'] === 'inativo' ? 'secondary' : 'warning'); 
                                            ?>">
                                                <?php echo ucfirst($equip['status'] ?? 'ativo'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($equip['localizacao'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($equip['area_planta'] ?? ''); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="visualizarEquipamentos.php?id=<?php echo $equip['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($is_admin): ?>
                                                <a href="formEquipamento.php?id=<?php echo $equip['id']; ?>" 
                                                   class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="excluirEquipamento.php?id=<?php echo $equip['id']; ?>" 
                                                   class="btn btn-sm btn-danger" title="Excluir"
                                                   onclick="return confirm('Tem certeza que deseja excluir este equipamento? Esta ação não pode ser desfeita.')">
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
        $('#tabelaEquipamentos').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.0/i18n/pt-BR.json"
            },
            "pageLength": 25,
            "order": [[1, "asc"]]
        });
    });
    </script>
    
    <?php
}

require_once 'footer.php'; 
?> 