<?php
require_once 'header.php';
require_once 'conexaoBD.php';

$editar = false;
$material = null;

if (isset($_GET['id'])) {
    $editar = true;
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM materials WHERE id = ?";
    $material = executarQuery($sql, [$id])->fetch();
    
    if (!$material) {
        header("Location: listarMateriais.php?msg=material_nao_encontrado");
        exit();
    }
}
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes"></i> 
                    <?php echo $editar ? 'Editar Material' : 'Novo Material'; ?>
                </h3>
            </div>
            <div class="card-body">
                <form action="actionMaterial.php" method="POST" id="formMaterial" enctype="multipart/form-data">
                    <?php if ($editar): ?>
                        <input type="hidden" name="id" value="<?php echo $material['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome">Nome do Material *</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo $editar ? $material['nome'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="codigo">Código</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" 
                                       value="<?php echo $editar ? $material['codigo'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unidade_medida">Unidade de Medida</label>
                                <select class="form-control" id="unidade_medida" name="unidade_medida">
                                    <option value="">Selecione...</option>
                                    <option value="un" <?php echo $editar && $material['unidade_medida'] === 'un' ? 'selected' : ''; ?>>
                                        Unidade (un)
                                    </option>
                                    <option value="kg" <?php echo $editar && $material['unidade_medida'] === 'kg' ? 'selected' : ''; ?>>
                                        Quilograma (kg)
                                    </option>
                                    <option value="L" <?php echo $editar && $material['unidade_medida'] === 'L' ? 'selected' : ''; ?>>
                                        Litro (L)
                                    </option>
                                    <option value="m" <?php echo $editar && $material['unidade_medida'] === 'm' ? 'selected' : ''; ?>>
                                        Metro (m)
                                    </option>
                                    <option value="m²" <?php echo $editar && $material['unidade_medida'] === 'm²' ? 'selected' : ''; ?>>
                                        Metro Quadrado (m²)
                                    </option>
                                    <option value="m³" <?php echo $editar && $material['unidade_medida'] === 'm³' ? 'selected' : ''; ?>>
                                        Metro Cúbico (m³)
                                    </option>
                                    <option value="g" <?php echo $editar && $material['unidade_medida'] === 'g' ? 'selected' : ''; ?>>
                                        Grama (g)
                                    </option>
                                    <option value="ml" <?php echo $editar && $material['unidade_medida'] === 'ml' ? 'selected' : ''; ?>>
                                        Mililitro (ml)
                                    </option>
                                    <option value="caixa" <?php echo $editar && $material['unidade_medida'] === 'caixa' ? 'selected' : ''; ?>>
                                        Caixa
                                    </option>
                                    <option value="rolo" <?php echo $editar && $material['unidade_medida'] === 'rolo' ? 'selected' : ''; ?>>
                                        Rolo
                                    </option>
                                    <option value="fardo" <?php echo $editar && $material['unidade_medida'] === 'fardo' ? 'selected' : ''; ?>>
                                        Fardo
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="codigo">Código Personalizado</label>
                                <input type="text" class="form-control" id="codigo_personalizado" name="codigo_personalizado" 
                                       value="<?php echo $editar ? $material['codigo'] : ''; ?>" 
                                       placeholder="Ex: MAT-ROL-6205">
                                <small class="form-text text-muted">Código interno para identificação</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="descricao">Descrição</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3" 
                                          placeholder="Descreva o material, especificações técnicas, etc."><?php echo $editar ? $material['descricao'] : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seção de Imagem -->
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="imagem">Imagem do Material</label>
                                <input type="file" class="form-control" id="imagem" name="imagem" accept="image/jpeg,image/jpg,image/png">
                                <small class="form-text text-muted">Formatos aceitos: JPG, JPEG, PNG. Máximo 10MB.</small>
                                <?php if ($editar && !empty($material['imagem'])): ?>
                                    <div class="mt-2">
                                        <small class="text-info">Imagem atual disponível</small>
                                        <br>
                                        <img src="<?php echo $material['imagem']; ?>" 
                                             class="img-fluid mt-1" style="max-width: 200px;" alt="Imagem do material">
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
                            <a href="listarMateriais.php" class="btn btn-secondary">
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
                    <li>Nome do Material</li>
                </ul>
                
                <p><strong>Unidades de Medida:</strong></p>
                <ul>
                    <li><strong>un:</strong> Unidade (peças, componentes)</li>
                    <li><strong>kg:</strong> Quilograma (pesos)</li>
                    <li><strong>L:</strong> Litro (líquidos)</li>
                    <li><strong>m:</strong> Metro (comprimentos)</li>
                    <li><strong>m²:</strong> Metro quadrado (áreas)</li>
                    <li><strong>m³:</strong> Metro cúbico (volumes)</li>
                    <li><strong>g:</strong> Grama (pesos pequenos)</li>
                    <li><strong>ml:</strong> Mililitro (líquidos pequenos)</li>
                    <li><strong>caixa:</strong> Caixa (embalagens)</li>
                    <li><strong>rolo:</strong> Rolo (fitas, cabos)</li>
                    <li><strong>fardo:</strong> Fardo (materiais empacotados)</li>
                </ul>
                
                <p><strong>Código:</strong></p>
                <ul>
                    <li>Opcional, mas recomendado</li>
                    <li>Facilita a identificação</li>
                    <li>Ex: MAT-ROL-6205, MAT-OLE-068</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 