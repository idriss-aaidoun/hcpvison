<?php
$pageTitle = 'Tableau de Bord - HCPVision';
include ROOT_PATH . '/app/views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt me-2"></i>
        Tableau de Bord
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <a href="/rapports/generate?type=dashboard&format=pdf" class="btn btn-sm btn-primary" target="_blank">
                <i class="fas fa-download"></i> Export PDF
            </a>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card kpi-card h-100">
            <div class="card-body text-center">
                <div class="kpi-number"><?php echo number_format($kpiData['total_enqueteurs'] ?? 0); ?></div>
                <div class="kpi-label">Enquêteurs Actifs</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card kpi-card h-100">
            <div class="card-body text-center">
                <div class="kpi-number"><?php echo number_format($kpiData['total_enquetes'] ?? 0); ?></div>
                <div class="kpi-label">Total Enquêtes</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card kpi-card h-100">
            <div class="card-body text-center">
                <div class="kpi-number"><?php echo number_format($kpiData['enquetes_terminees'] ?? 0); ?></div>
                <div class="kpi-label">Enquêtes Terminées</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card kpi-card h-100">
            <div class="card-body text-center">
                <div class="kpi-number"><?php echo number_format($kpiData['duree_moyenne_globale'] ?? 0, 1); ?></div>
                <div class="kpi-label">Durée Moy. (min)</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Status Distribution -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Répartition des Statuts
                </h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshChart('status')">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Collection Trends -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Tendance de Collecte (30 jours)
                </h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshChart('collection')">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="collectionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Tables Row -->
<div class="row mb-4">
    <!-- Top Enquêteurs -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-trophy me-2"></i>
                    Top Enquêteurs
                </h5>
                <a href="/enqueteurs" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Rang</th>
                                <th>Enquêteur</th>
                                <th>Enquêtes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($topEnqueteurs)): ?>
                                <?php foreach ($topEnqueteurs as $index => $enqueteur): ?>
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
                                            <a href="/enqueteurs/detail?ninv=<?php echo urlencode($enqueteur['ninv']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($enqueteur['enq_nom']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo number_format($enqueteur['nbr_enquetes']); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Aucune donnée disponible</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Avancement par Région -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-map-marked-alt me-2"></i>
                    Avancement par Région
                </h5>
                <a href="/regions" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Région</th>
                                <th>Total</th>
                                <th>Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($regionCompletion)): ?>
                                <?php foreach (array_slice($regionCompletion, 0, 8) as $region): ?>
                                    <tr>
                                        <td>
                                            <a href="/regions/detail?code=<?php echo urlencode($region['region']); ?>" class="text-decoration-none">
                                                Région <?php echo htmlspecialchars($region['region']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo number_format($region['total_enquetes']); ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar <?php echo $region['taux_completion'] > 80 ? 'bg-success' : ($region['taux_completion'] > 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $region['taux_completion']; ?>%"
                                                     aria-valuenow="<?php echo $region['taux_completion']; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?php echo number_format($region['taux_completion'], 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Aucune donnée disponible</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Activité Récente
                </h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary active" data-filter="all">Tout</button>
                    <button class="btn btn-sm btn-outline-secondary" data-filter="completed">Terminées</button>
                    <button class="btn btn-sm btn-outline-secondary" data-filter="in_progress">En cours</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Enquêteur</th>
                                <th>Région</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentSurveys)): ?>
                                <?php foreach ($recentSurveys as $survey): ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($survey['dd'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="/enqueteurs/detail?ninv=<?php echo urlencode($survey['ninv']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($survey['enq_nom']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">Région <?php echo htmlspecialchars($survey['reg']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch ($survey['statut_libelle']) {
                                                case 'Terminé':
                                                    $statusClass = 'bg-success';
                                                    break;
                                                case 'EN COURS':
                                                    $statusClass = 'bg-warning';
                                                    break;
                                                case 'Partiel':
                                                    $statusClass = 'bg-info';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($survey['statut_libelle']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewSurveyDetails('<?php echo $survey['c99-id']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Aucune activité récente</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quality Indicators -->
<?php if (isset($dataQuality)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-check-circle me-2"></i>
                    Indicateurs de Qualité
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-primary"><?php echo number_format($dataQuality['anomalies_count']); ?></div>
                            <small class="text-muted">Anomalies Détectées</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-info"><?php echo number_format($dataQuality['suppression_rate'], 1); ?>%</div>
                            <small class="text-muted">Taux de Suppression</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-success"><?php echo number_format($productivityMetrics['enquetes_par_enqueteur'] ?? 0, 1); ?></div>
                            <small class="text-muted">Enquêtes/Enquêteur</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-warning"><?php echo number_format($productivityMetrics['duree_moyenne'] ?? 0, 1); ?></div>
                            <small class="text-muted">Durée Moy. (min)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Dashboard-specific JavaScript
let statusChart, collectionChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboardCharts();
    initializeActivityFilters();
});

function initializeDashboardCharts() {
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        fetch('/api/dashboard/status')
            .then(response => response.json())
            .then(data => {
                statusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.values,
                            backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545', '#6c757d'],
                            borderWidth: 0
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
            })
            .catch(error => console.error('Error loading status data:', error));
    }
    
    // Collection Trends Chart
    const collectionCtx = document.getElementById('collectionChart');
    if (collectionCtx) {
        fetch('/api/dashboard/collection-trends')
            .then(response => response.json())
            .then(data => {
                collectionChart = new Chart(collectionCtx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Enquêtes par jour',
                            data: data.values,
                            borderColor: 'rgba(102, 126, 234, 1)',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true
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
            })
            .catch(error => console.error('Error loading collection trends:', error));
    }
}

function initializeActivityFilters() {
    const filterButtons = document.querySelectorAll('[data-filter]');
    const tableRows = document.querySelectorAll('tbody tr');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter table rows
            tableRows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else {
                    const statusBadge = row.querySelector('.badge');
                    if (statusBadge) {
                        const status = statusBadge.textContent.trim();
                        const shouldShow = 
                            (filter === 'completed' && status === 'Terminé') ||
                            (filter === 'in_progress' && status === 'EN COURS');
                        row.style.display = shouldShow ? '' : 'none';
                    }
                }
            });
        });
    });
}

function viewSurveyDetails(surveyId) {
    // This would open a modal or navigate to survey details
    console.log('View survey details:', surveyId);
    // For now, just show an alert
    alert('Fonctionnalité de détail d\'enquête à implémenter');
}

function refreshChart(chartType) {
    if (chartType === 'status' && statusChart) {
        statusChart.destroy();
        initializeDashboardCharts();
    } else if (chartType === 'collection' && collectionChart) {
        collectionChart.destroy();
        initializeDashboardCharts();
    }
}
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
