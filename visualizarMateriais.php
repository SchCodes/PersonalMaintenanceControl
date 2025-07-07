<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

// Se foi passado um ID específico, mostrar detalhes do material
if (isset($_GET['id'])) {
    $material_id = (int)$_GET['id'];
    
    // Buscar dados do material
    $sql = "SELECT * FROM materials WHERE id = ?";
    $stmt = executarQuery($sql, [$material_id]);
    $material = $stmt->fetch();
    
    if (!$material) {
        header('Location: listarMateriais.php?msg=material_nao_encontrado');
        exit;
    }
    ?>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-box"></i> Detalhes do Material
                    </h3>
                    <div class="card-tools">
                        <a href="<?php echo $is_admin ? 'listarMateriais.php' : 'listarMateriaisTecnico.php'; ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                        <?php if ($is_admin): ?>
                        <a href="formMaterial.php?id=<?php echo $material['id']; ?>" class="btn btn-warning btn-sm">
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
                                    <td><?php echo $material['id']; ?></td>
                                </tr>
                                <tr>
                                    <th>Nome:</th>
                                    <td><?php echo htmlspecialchars($material['nome']); ?></td>
                                </tr>
                                <tr>
                                    <th>Código:</th>
                                    <td><?php echo htmlspecialchars($material['codigo'] ?? 'Não informado'); ?></td>
                                </tr>
                                <tr>
                                    <th>Unidade de Medida:</th>
                                    <td><?php echo htmlspecialchars($material['unidade_medida'] ?? 'Não informado'); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Descrição</h5>
                            <p><?php echo !empty($material['descricao']) ? nl2br(htmlspecialchars($material['descricao'])) : 'Nenhuma descrição disponível.'; ?></p>
                        </div>
                    </div>
                    
                    <!-- Seção de Imagem -->
                    <?php if (!empty($material['imagem'])): ?>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h5>Imagem do Material</h5>
                            <div class="text-center">
                                <?php if (file_exists($material['imagem'])): ?>
                                    <img src="<?php echo $material['imagem']; ?>" 
                                         class="img-fluid" style="max-width: 400px; height: auto;" alt="Imagem do material">
                                <?php else: ?>
                                    <p class="text-muted">Imagem não encontrada</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php
} else {
    // Listagem geral de materiais (para técnicos)
    // Buscar materiais
    $sql = "SELECT * FROM materials ORDER BY nome";
    $materiais = executarQuery($sql)->fetchAll();
    ?>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-boxes"></i> Materiais
                    </h3>
                    <div class="card-tools">
                        <?php if ($is_admin): ?>
                        <a href="formMaterial.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Novo Material
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (empty($materiais)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhum material cadastrado.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tabelaMateriais">
                                <thead>
                                    <tr>
                                        <th>Imagem</th>
                                        <th>Código</th>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Unidade de Medida</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materiais as $mat): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($mat['imagem'])): ?>
                                                <?php if (file_exists($mat['imagem'])): ?>
                                                    <img src="<?php echo $mat['imagem']; ?>" 
                                                         class="img-thumbnail" style="max-width: 50px; height: auto;" 
                                                         alt="Imagem do material" title="<?php echo htmlspecialchars($mat['nome']); ?>">
                                                <?php else: ?>
                                                    <span class="text-muted">Imagem não encontrada</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <svg width="50" height="50" viewBox="0 0 24 24" fill="currentColor" class="text-muted">
                                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                                </svg>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($mat['codigo'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($mat['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($mat['descricao'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($mat['unidade_medida'] ?? ''); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="visualizarMateriais.php?id=<?php echo $mat['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($is_admin): ?>
                                                <a href="formMaterial.php?id=<?php echo $mat['id']; ?>" 
                                                   class="btn btn-sm btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="excluirMaterial.php?id=<?php echo $mat['id']; ?>" 
                                                   class="btn btn-sm btn-danger" title="Excluir"
                                                   onclick="return confirm('Tem certeza que deseja excluir este material? Esta ação não pode ser desfeita.')">
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
        $('#tabelaMateriais').DataTable({
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