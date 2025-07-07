<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

$editar = false;
$equipamento = null;

if (isset($_GET['id'])) {
    $editar = true;
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM equipment WHERE id = ?";
    $equipamento = executarQuery($sql, [$id])->fetch();
    
    if (!$equipamento) {
        header("Location: listarEquipamentos.php?msg=equipamento_nao_encontrado");
        exit();
    }
}
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs"></i> 
                    <?php echo $editar ? 'Editar Equipamento' : 'Novo Equipamento'; ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form action="actionEquipamento.php" method="POST" enctype="multipart/form-data" id="formEquipamento">
                    <?php if ($editar): ?>
                        <input type="hidden" name="id" value="<?php echo $equipamento['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome">Nome do Equipamento *</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo $editar ? $equipamento['nome'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="codigo">Código *</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" 
                                       value="<?php echo $editar ? $equipamento['codigo'] : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo">Tipo</label>
                                <input type="text" class="form-control" id="tipo" name="tipo" 
                                       value="<?php echo $editar ? $equipamento['tipo'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="ativo" <?php echo $editar && $equipamento['status'] === 'ativo' ? 'selected' : ''; ?>>
                                        Ativo
                                    </option>
                                    <option value="inativo" <?php echo $editar && $equipamento['status'] === 'inativo' ? 'selected' : ''; ?>>
                                        Inativo
                                    </option>
                                    <option value="manutencao" <?php echo $editar && $equipamento['status'] === 'manutencao' ? 'selected' : ''; ?>>
                                        Em Manutenção
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="localizacao">Localização</label>
                                <input type="text" class="form-control" id="localizacao" name="localizacao" 
                                       value="<?php echo $editar ? $equipamento['localizacao'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="area_planta">Área da Planta</label>
                                <input type="text" class="form-control" id="area_planta" name="area_planta" 
                                       value="<?php echo $editar ? $equipamento['area_planta'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="descricao">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo $editar ? $equipamento['descricao'] : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="imagem">Imagem do Equipamento</label>
                                <input type="file" class="form-control" id="imagem" name="imagem" accept="image/jpeg,image/jpg,image/png">
                                <small class="form-text text-muted">
                                    Formatos aceitos: JPG, JPEG, PNG. Máximo 10MB.
                                </small>
                                <?php if ($editar && !empty($equipamento['imagem'])): ?>
                                    <div class="mt-2">
                                        <small class="text-info">Imagem atual disponível</small>
                                        <br>
                                        <img src="<?php echo $equipamento['imagem']; ?>" 
                                             class="img-fluid mt-1" style="max-width: 200px;" alt="Imagem do equipamento">
                                        <br>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="excluir_imagem" name="excluir_imagem">
                                            <label class="form-check-label" for="excluir_imagem">
                                                Excluir imagem atual
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?php echo $editar ? 'Atualizar' : 'Cadastrar'; ?>
                            </button>
                            <a href="listarEquipamentos.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Informações
                </h3>
            </div>
            <div class="card-body">
                <p><strong>Campos Obrigatórios:</strong></p>
                <ul>
                    <li>Nome do Equipamento</li>
                    <li>Código (único)</li>
                </ul>
                
                <p><strong>Status:</strong></p>
                <ul>
                    <li><strong>Ativo:</strong> Equipamento em operação</li>
                    <li><strong>Inativo:</strong> Equipamento fora de operação</li>
                    <li><strong>Em Manutenção:</strong> Equipamento em manutenção</li>
                </ul>
                
                <p><strong>Imagem:</strong></p>
                <ul>
                    <li>Formatos: JPG, JPEG, PNG</li>
                    <li>Tamanho máximo: 10MB</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 