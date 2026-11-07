<?php
$pageTitle = 'Région ' . htmlspecialchars($regionCode ?? 'Inconnue') . ' - Détails - HCPVision';
include ROOT_PATH . '/app/views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/regions">Régions</a></li>
                <li class="breadcrumb-item active">Région <?php echo htmlspecialchars($regionCode ?? 'Inconnue'); ?></li>
            </ol>
        </nav>
        <h1 class="h2">
            <i class="fas fa-map-marked-alt me-2"></i>
            Région <?php echo htmlspecialchars($regionCode ?? 'Inconnue'); ?>
            <small class="text-muted">- Analyse Détaillée</small>
        </h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/regions" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <a href="/regions/export?region=<?php echo urlencode($regionCode); ?>&format=pdf" 
               class="btn btn-sm btn-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>
</div>

<!-- Region Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($regionStats['total_enquetes'] ?? 0); ?></h4>
                <p class="mb-0">Total Enquêtes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($regionStats['enquetes_terminees'] ?? 0); ?></h4>
                <p class="mb-0">Enquêtes Terminées</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($regionStats['taux_completion'] ?? 0, 1); ?>%</h4>
                <p class="mb-0">Taux Completion</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4><?php echo count($regionStats['enqueteurs'] ?? []); ?></h4>
                <p class="mb-0">Enquêteurs Actifs</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Status Distribution -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Répartition des Statuts
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($statusDistribution)): ?>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune donnée de statut disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Milieu Distribution -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-city me-2"></i>
                    Répartition Urbain/Rural
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($regionStats['milieu_distribution'])): ?>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="milieuChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-city fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune donnée de milieu disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Performance Trends -->
<?php if (!empty($performanceTrends)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Évolution de la Performance (30 derniers jours)
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Top Provinces -->
<?php if (!empty($topProvinces)): ?>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-trophy me-2"></i>
                    Top Provinces
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Rang</th>
                                <th>Province</th>
                                <th>Total</th>
                                <th>Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProvinces as $index => $province): ?>
                                <tr>
                                    <td>
                                        <?php if ($index === 0): ?>
                                            <i class="fas fa-trophy text-warning"></i>
                                        <?php elseif ($index === 1): ?>
                                            <i class="fas fa-medal text-secondary"></i>
                                        <?php elseif ($index === 2): ?>
                                            <i class="fas fa-award text-warning"></i>
                                        <?php else: ?>
                                            <?php echo $index + 1; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>Province <?php echo htmlspecialchars($province['province']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo number_format($province['total_enquetes']); ?></span>
                                    </td>
                                    <td>
                                        <div class="progress" style="width: 60px; height: 15px;">
                                            <div class="progress-bar <?php echo $province['taux_completion'] > 80 ? 'bg-success' : ($province['taux_completion'] > 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                 style="width: <?php echo $province['taux_completion']; ?>%">
                                            </div>
                                        </div>
                                        <small><?php echo number_format($province['taux_completion'], 1); ?>%</small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Duration Statistics -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Statistiques de Durée
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($durationStats)): ?>
                    <div class="row">
                        <div class="col-6 text-center p-3">
                            <h4 class="text-info"><?php echo number_format($durationStats['duree_moyenne'] ?? 0, 1); ?></h4>
                            <small class="text-muted">Durée Moyenne (min)</small>
                        </div>
                        <div class="col-6 text-center p-3">
                            <h4 class="text-success"><?php echo number_format($durationStats['duree_min'] ?? 0); ?></h4>
                            <small class="text-muted">Durée Minimale (min)</small>
                        </div>
                        <div class="col-6 text-center p-3">
                            <h4 class="text-warning"><?php echo number_format($durationStats['duree_max'] ?? 0); ?></h4>
                            <small class="text-muted">Durée Maximale (min)</small>
                        </div>
                        <div class="col-6 text-center p-3">
                            <h4 class="text-secondary"><?php echo number_format($durationStats['duree_stddev'] ?? 0, 1); ?></h4>
                            <small class="text-muted">Écart-Type (min)</small>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune donnée de durée disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Enqueteurs in Region -->
<?php if (!empty($regionStats['enqueteurs'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    Enquêteurs de la Région
                </h5>
                <span class="badge bg-secondary"><?php echo count($regionStats['enqueteurs']); ?> enquêteurs</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>NINV</th>
                                <th>Nom</th>
                                <th>Total Enquêtes</th>
                                <th>Terminées</th>
                                <th>Taux Completion</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($regionStats['enqueteurs'] as $enqueteur): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($enqueteur['ninv']); ?></code></td>
                                    <td>
                                        <a href="/enqueteurs/detail?ninv=<?php echo urlencode($enqueteur['ninv']); ?>" class="text-decoration-none">
                                            <strong><?php echo htmlspecialchars($enqueteur['enq_nom']); ?></strong>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo number_format($enqueteur['total_enquetes']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?php echo number_format($enqueteur['enquetes_terminees']); ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 60px; height: 15px;">
                                                <div class="progress-bar <?php echo $enqueteur['taux_completion'] > 80 ? 'bg-success' : ($enqueteur['taux_completion'] > 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                     style="width: <?php echo $enqueteur['taux_completion']; ?>%">
                                                </div>
                                            </div>
                                            <small><?php echo number_format($enqueteur['taux_completion'], 1); ?>%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="/enqueteurs/detail?ninv=<?php echo urlencode($enqueteur['ninv']); ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Surveys -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>
                    Enquêtes Récentes
                </h5>
                <span class="badge bg-secondary"><?php echo count($recentSurveys ?? []); ?> enquêtes</span>
            </div>
            <div class="card-body">
                <?php if (!empty($recentSurveys)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Enquêteur</th>
                                    <th>Province</th>
                                    <th>Durée</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentSurveys, 0, 20) as $survey): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo $survey['dd'] ? date('d/m/Y H:i', strtotime($survey['dd'])) : '-'; ?></small>
                                        </td>
                                        <td>
                                            <a href="/enqueteurs/detail?ninv=<?php echo urlencode($survey['ninv']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($survey['enq_nom']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">Province <?php echo htmlspecialchars($survey['pro'] ?? ''); ?></span>
                                        </td>
                                        <td><?php echo ($survey['dureeintervw'] ?? 0) > 0 ? $survey['dureeintervw'] . ' min' : '-'; ?></td>
                                        <td>
                                            <?php
                                            $statusClass = 'secondary';
                                            switch ($survey['statut_libelle'] ?? '') {
                                                case 'Terminé':
                                                    $statusClass = 'success';
                                                    break;
                                                case 'EN COURS':
                                                    $statusClass = 'warning';
                                                    break;
                                                case 'Partiel':
                                                    $statusClass = 'info';
                                                    break;
                                                case 'Refus':
                                                    $statusClass = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($survey['statut_libelle'] ?? 'Inconnu'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5>Aucune enquête</h5>
                        <p class="text-muted">Aucune enquête récente dans cette région.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeRegionDetailCharts();
});

function initializeRegionDetailCharts() {
    // Status Distribution Chart
    <?php if (!empty($statusDistribution)): ?>
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusData = <?php echo json_encode($statusDistribution); ?>;
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.statut_libelle),
                datasets: [{
                    data: statusData.map(item => item.count),
                    backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545', '#6c757d']
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
    }
    <?php endif; ?>
    
    // Milieu Distribution Chart
    <?php if (!empty($regionStats['milieu_distribution'])): ?>
    const milieuCtx = document.getElementById('milieuChart');
    if (milieuCtx) {
        const milieuData = <?php echo json_encode($regionStats['milieu_distribution']); ?>;
        let urbainCount = 0;
        let ruralCount = 0;
        
        milieuData.forEach(item => {
            if (item.milieu == '1') {
                urbainCount += parseInt(item.total_enquetes);
            } else if (item.milieu == '2') {
                ruralCount += parseInt(item.total_enquetes);
            }
        });
        
        new Chart(milieuCtx, {
            type: 'doughnut',
            data: {
                labels: ['Urbain', 'Rural'],
                datasets: [{
                    data: [urbainCount, ruralCount],
                    backgroundColor: ['#007bff', '#28a745']
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
    }
    <?php endif; ?>
    
    // Performance Trends Chart
    <?php if (!empty($performanceTrends)): ?>
    const trendsCtx = document.getElementById('trendsChart');
    if (trendsCtx) {
        const trendsData = <?php echo json_encode($performanceTrends); ?>;
        
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: trendsData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('fr-FR');
                }),
                datasets: [{
                    label: 'Enquêtes quotidiennes',
                    data: trendsData.map(item => item.daily_surveys),
                    borderColor: 'rgba(102, 126, 234, 1)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: false
                }, {
                    label: 'Enquêtes cumulées',
                    data: trendsData.map(item => item.cumulative_surveys),
                    borderColor: 'rgba(118, 75, 162, 1)',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Enquêtes quotidiennes'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Enquêtes cumulées'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    }
    <?php endif; ?>
}
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
