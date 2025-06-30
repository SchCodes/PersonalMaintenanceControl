<?php
require_once 'validarSessao.php';
require_once 'conexaoBD.php';

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

require_once 'header.php';

// Estatísticas gerais
$sql_total_atividades = $is_admin ? 
    "SELECT COUNT(*) as total FROM maintenance_activities" :
    "SELECT COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ?";
$params_total = $is_admin ? [] : [$usuario_id];
$total_atividades = executarQuery($sql_total_atividades, $params_total)->fetch()['total'];

// Atividades por status
$sql_status = $is_admin ?
    "SELECT status, COUNT(*) as total FROM maintenance_activities GROUP BY status" :
    "SELECT status, COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ? GROUP BY status";
$params_status = $is_admin ? [] : [$usuario_id];
$atividades_por_status = executarQuery($sql_status, $params_status)->fetchAll();

// Atividades por tipo
$sql_tipo = $is_admin ?
    "SELECT tipo_manutencao, COUNT(*) as total FROM maintenance_activities GROUP BY tipo_manutencao" :
    "SELECT tipo_manutencao, COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ? GROUP BY tipo_manutencao";
$params_tipo = $is_admin ? [] : [$usuario_id];
$atividades_por_tipo = executarQuery($sql_tipo, $params_tipo)->fetchAll();

// Atividades do mês atual
$sql_mes = $is_admin ?
    "SELECT COUNT(*) as total FROM maintenance_activities WHERE MONTH(data_inicio) = MONTH(CURRENT_DATE()) AND YEAR(data_inicio) = YEAR(CURRENT_DATE())" :
    "SELECT COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ? AND MONTH(data_inicio) = MONTH(CURRENT_DATE()) AND YEAR(data_inicio) = YEAR(CURRENT_DATE())";
$params_mes = $is_admin ? [] : [$usuario_id];
$atividades_mes = executarQuery($sql_mes, $params_mes)->fetch()['total'];

// Atividades concluídas
$sql_concluidas = $is_admin ?
    "SELECT COUNT(*) as total FROM maintenance_activities WHERE status = 'concluida'" :
    "SELECT COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ? AND status = 'concluida'";
$params_concluidas = $is_admin ? [] : [$usuario_id];
$atividades_concluidas = executarQuery($sql_concluidas, $params_concluidas)->fetch()['total'];

// Atividades em andamento
$sql_andamento = $is_admin ?
    "SELECT COUNT(*) as total FROM maintenance_activities WHERE status = 'em_andamento'" :
    "SELECT COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ? AND status = 'em_andamento'";
$params_andamento = $is_admin ? [] : [$usuario_id];
$atividades_andamento = executarQuery($sql_andamento, $params_andamento)->fetch()['total'];

// Total de equipamentos
$sql_equipamentos = "SELECT COUNT(*) as total FROM equipment WHERE status = 'ativo'";
$total_equipamentos = executarQuery($sql_equipamentos)->fetch()['total'];

// Total de materiais
$sql_materiais = "SELECT COUNT(*) as total FROM materials";
$total_materiais = executarQuery($sql_materiais)->fetch()['total'];

// Top equipamentos com mais manutenções
$sql_equipamentos = $is_admin ?
    "SELECT e.nome, COUNT(ma.id) as total 
     FROM equipment e 
     LEFT JOIN maintenance_activities ma ON e.id = ma.equipamento_id 
     GROUP BY e.id, e.nome 
     ORDER BY total DESC 
     LIMIT 5" :
    "SELECT e.nome, COUNT(ma.id) as total 
     FROM equipment e 
     LEFT JOIN maintenance_activities ma ON e.id = ma.equipamento_id AND ma.usuario_id = ?
     GROUP BY e.id, e.nome 
     ORDER BY total DESC 
     LIMIT 5";
$params_equip = $is_admin ? [] : [$usuario_id];
$top_equipamentos = executarQuery($sql_equipamentos, $params_equip)->fetchAll();

// Atividades por mês (últimos 6 meses)
$sql_mensal = $is_admin ?
    "SELECT DATE_FORMAT(data_inicio, '%Y-%m') as mes, COUNT(*) as total 
     FROM maintenance_activities 
     WHERE data_inicio >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(data_inicio, '%Y-%m') 
     ORDER BY mes" :
    "SELECT DATE_FORMAT(data_inicio, '%Y-%m') as mes, COUNT(*) as total 
     FROM maintenance_activities 
     WHERE usuario_id = ? AND data_inicio >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(data_inicio, '%Y-%m') 
     ORDER BY mes";
$params_mensal = $is_admin ? [] : [$usuario_id];
$atividades_mensal = executarQuery($sql_mensal, $params_mensal)->fetchAll();

// Atividades por prioridade
$sql_prioridade = $is_admin ?
    "SELECT prioridade, COUNT(*) as total FROM maintenance_activities GROUP BY prioridade" :
    "SELECT prioridade, COUNT(*) as total FROM maintenance_activities WHERE usuario_id = ? GROUP BY prioridade";
$params_prioridade = $is_admin ? [] : [$usuario_id];
$atividades_prioridade = executarQuery($sql_prioridade, $params_prioridade)->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i> Dashboard de Relatórios
                </h3>
                <div class="card-tools">
                    <span class="badge badge-info"><?php echo date('d/m/Y H:i'); ?></span>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Cards de KPI Principais -->
                <div class="row mb-4">
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3><?php echo $total_atividades; ?></h3>
                                <p>Total de Atividades</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $atividades_concluidas; ?></h3>
                                <p>Concluídas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $atividades_andamento; ?></h3>
                                <p>Em Andamento</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $atividades_mes; ?></h3>
                                <p>Este Mês</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3><?php echo $total_equipamentos; ?></h3>
                                <p>Equipamentos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="small-box bg-dark">
                            <div class="inner">
                                <h3><?php echo $total_materiais; ?></h3>
                                <p>Materiais</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos Principais -->
                <div class="row">
                    <!-- Gráfico de Status -->
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-pie"></i> Atividades por Status
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartStatus" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gráfico de Tipo -->
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-bar"></i> Atividades por Tipo
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartTipo" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos Secundários -->
                <div class="row">
                    <!-- Gráfico de Prioridade -->
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-exclamation-triangle"></i> Atividades por Prioridade
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartPrioridade" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gráfico Mensal -->
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-line"></i> Atividades por Mês (Últimos 6 meses)
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartMensal" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Equipamentos -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-trophy"></i> Top 5 Equipamentos com Mais Manutenções
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($top_equipamentos)): ?>
                                    <p class="text-muted text-center">Nenhum equipamento encontrado.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Posição</th>
                                                    <th>Equipamento</th>
                                                    <th>Manutenções</th>
                                                    <th>Progresso</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $max_manutencoes = max(array_column($top_equipamentos, 'total'));
                                                foreach ($top_equipamentos as $index => $equip): 
                                                    $percentual = $max_manutencoes > 0 ? ($equip['total'] / $max_manutencoes) * 100 : 0;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-<?php echo $index < 3 ? 'warning' : 'secondary'; ?>">
                                                            <?php echo $index + 1; ?>º
                                                        </span>
                                                    </td>
                                                    <td><strong><?php echo $equip['nome']; ?></strong></td>
                                                    <td>
                                                        <span class="badge badge-info"><?php echo $equip['total']; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar bg-info" 
                                                                 style="width: <?php echo $percentual; ?>%">
                                                                <?php echo round($percentual); ?>%
                                                            </div>
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
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Dados para os gráficos
const dadosStatus = <?php echo json_encode($atividades_por_status); ?>;
const dadosTipo = <?php echo json_encode($atividades_por_tipo); ?>;
const dadosPrioridade = <?php echo json_encode($atividades_prioridade); ?>;
const dadosMensal = <?php echo json_encode($atividades_mensal); ?>;

// Configuração de cores
const cores = {
    status: ['#28a745', '#ffc107', '#17a2b8', '#dc3545'],
    tipo: ['#007bff', '#dc3545', '#ffc107', '#6c757d'],
    prioridade: ['#28a745', '#ffc107', '#fd7e14', '#dc3545']
};

// Gráfico de Status (Doughnut)
const ctxStatus = document.getElementById('chartStatus').getContext('2d');
new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
        labels: dadosStatus.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
        datasets: [{
            data: dadosStatus.map(item => item.total),
            backgroundColor: cores.status,
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfico de Tipo (Bar)
const ctxTipo = document.getElementById('chartTipo').getContext('2d');
new Chart(ctxTipo, {
    type: 'bar',
    data: {
        labels: dadosTipo.map(item => item.tipo_manutencao.charAt(0).toUpperCase() + item.tipo_manutencao.slice(1)),
        datasets: [{
            label: 'Quantidade',
            data: dadosTipo.map(item => item.total),
            backgroundColor: cores.tipo,
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Gráfico de Prioridade (Pie)
const ctxPrioridade = document.getElementById('chartPrioridade').getContext('2d');
new Chart(ctxPrioridade, {
    type: 'pie',
    data: {
        labels: dadosPrioridade.map(item => item.prioridade.charAt(0).toUpperCase() + item.prioridade.slice(1)),
        datasets: [{
            data: dadosPrioridade.map(item => item.total),
            backgroundColor: cores.prioridade,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfico Mensal (Line)
const ctxMensal = document.getElementById('chartMensal').getContext('2d');
new Chart(ctxMensal, {
    type: 'line',
    data: {
        labels: dadosMensal.map(item => {
            const [ano, mes] = item.mes.split('-');
            const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            return `${meses[parseInt(mes)-1]}/${ano}`;
        }),
        datasets: [{
            label: 'Atividades',
            data: dadosMensal.map(item => item.total),
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>

<?php require_once 'footer.php'; ?> 