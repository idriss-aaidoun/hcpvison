<?php
$pageTitle = 'Régions - HCPVision';
include ROOT_PATH . '/app/views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-map-marked-alt me-2"></i>
        Analyse Régionale
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <a href="/regions/export?format=csv" class="btn btn-sm btn-success" target="_blank">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
            <a href="/rapports/generate?type=regions&format=pdf" class="btn btn-sm btn-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Régions</h6>
                        <h3><?php echo count($regions); ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-map fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Enquêtes Totales</h6>
                        <h3><?php echo number_format(array_sum(array_column($regions, 'total_enquetes'))); ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Enquêtes Terminées</h6>
                        <h3><?php echo number_format(array_sum(array_column($regions, 'enquetes_terminees'))); ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Taux Moy. Global</h6>
                        <h3>
                            <?php 
                            $totalEnquetes = array_sum(array_column($regions, 'total_enquetes'));
                            $totalTerminees = array_sum(array_column($regions, 'enquetes_terminees'));
                            echo $totalEnquetes > 0 ? number_format(($totalTerminees / $totalEnquetes) * 100, 1) : '0';
                            ?>%
                        </h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-percentage fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Regional Progress Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Taux de Completion par Région
                </h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshChart('region')">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="regionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Regions Comparison Table -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-table me-2"></i>
            Comparaison des Régions
        </h5>
        <span class="badge bg-secondary"><?php echo count($regions); ?> régions</span>
    </div>
    <div class="card-body">
        <?php if (!empty($regions)): ?>
            <div class="table-responsive">
                <table class="table table-hover data-table">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Région</th>
                            <th>Total Enquêtes</th>
                            <th>Terminées</th>
                            <th>Taux Completion</th>
                            <th>Enquêteurs</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Sort regions by completion rate for ranking
                        $sortedRegions = $regions;
                        usort($sortedRegions, fn($a, $b) => $b['taux_completion'] <=> $a['taux_completion']);
                        ?>
                        <?php foreach ($sortedRegions as $index => $region): ?>
                            <tr>
                                <td>
                                    <?php if ($index === 0): ?>
                                        <i class="fas fa-trophy text-warning" title="Meilleure région"></i>
                                    <?php elseif ($index === 1): ?>
                                        <i class="fas fa-medal text-secondary" title="2ème place"></i>
                                    <?php elseif ($index === 2): ?>
                                        <i class="fas fa-award text-warning" title="3ème place"></i>
                                    <?php else: ?>
                                        <?php echo $index + 1; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>Région <?php echo htmlspecialchars($region['region']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo number_format($region['total_enquetes']); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?php echo number_format($region['enquetes_terminees']); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress me-2" style="width: 100px; height: 20px;">
                                            <div class="progress-bar <?php echo $region['taux_completion'] > 80 ? 'bg-success' : ($region['taux_completion'] > 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $region['taux_completion']; ?>%">
                                            </div>
                                        </div>
                                        <strong><?php echo number_format($region['taux_completion'], 1); ?>%</strong>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $enqueteursRegion = array_filter($enqueteursCount, fn($e) => $e['region'] == $region['region']);
                                    $nbEnqueteurs = !empty($enqueteursRegion) ? reset($enqueteursRegion)['nb_enqueteurs'] : 0;
                                    ?>
                                    <span class="badge bg-info"><?php echo $nbEnqueteurs; ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/regions/detail?code=<?php echo urlencode($region['region']); ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/regions/export?region=<?php echo urlencode($region['region']); ?>&format=csv" 
                                           class="btn btn-outline-success" 
                                           title="Export CSV">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-map fa-3x text-muted mb-3"></i>
                <h5>Aucune donnée régionale</h5>
                <p class="text-muted">Aucune donnée régionale n'est disponible pour le moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Provinces Overview -->
<?php if (!empty($provinces)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-map-marked me-2"></i>
                    Aperçu des Provinces
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php 
                    // Group provinces by region
                    $provincesByRegion = [];
                    foreach ($provinces as $province) {
                        $provincesByRegion[$province['region']][] = $province;
                    }
                    ?>
                    <?php foreach ($provincesByRegion as $regionCode => $regionProvinces): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-map-pin me-1"></i>
                                        Région <?php echo htmlspecialchars($regionCode); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <?php foreach (array_slice($regionProvinces, 0, 5) as $province): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center p-2">
                                                <small>Province <?php echo htmlspecialchars($province['province']); ?></small>
                                                <span class="badge bg-primary"><?php echo number_format($province['total_enquetes']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($regionProvinces) > 5): ?>
                                            <div class="list-group-item p-2 text-center">
                                                <small class="text-muted">
                                                    +<?php echo count($regionProvinces) - 5; ?> autres provinces
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Milieu Distribution -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Répartition Urbain/Rural
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($milieuDistribution)): ?>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="milieuChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune donnée de milieu disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    Enquêteurs par Région
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($enqueteursCount)): ?>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="enqueteursChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune donnée d'enquêteurs disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeRegionCharts();
});

function initializeRegionCharts() {
    // Regional Progress Chart
    const regionCtx = document.getElementById('regionChart');
    if (regionCtx) {
        fetch('/api/regions/progress')
            .then(response => response.json())
            .then(data => {
                new Chart(regionCtx, {
                    type: 'horizontalBar',
                    data: {
                        labels: data.labels || [],
                        datasets: [{
                            label: 'Taux de completion (%)',
                            data: data.taux_completion || [],
                            backgroundColor: data.taux_completion ? data.taux_completion.map(rate => 
                                rate > 80 ? '#28a745' : (rate > 50 ? '#ffc107' : '#dc3545')
                            ) : []
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Taux de completion (%)'
                                }
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
            .catch(error => console.error('Error loading region data:', error));
    }
    
    // Milieu Distribution Chart
    const milieuCtx = document.getElementById('milieuChart');
    if (milieuCtx) {
        fetch('/api/regions/milieu-distribution')
            .then(response => response.json())
            .then(data => {
                new Chart(milieuCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels || [],
                        datasets: [{
                            data: data.values || [],
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
            })
            .catch(error => console.error('Error loading milieu data:', error));
    }
    
    // Enqueteurs Chart
    const enqueteursCtx = document.getElementById('enqueteursChart');
    if (enqueteursCtx) {
        const enqueteursData = <?php echo json_encode($enqueteursCount); ?>;
        
        new Chart(enqueteursCtx, {
            type: 'bar',
            data: {
                labels: enqueteursData.map(item => 'Région ' + item.region),
                datasets: [{
                    label: 'Nombre d\'enquêteurs',
                    data: enqueteursData.map(item => item.nb_enqueteurs),
                    backgroundColor: 'rgba(102, 126, 234, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Nombre d\'enquêteurs'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
}

function refreshChart(chartType) {
    location.reload();
}
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
