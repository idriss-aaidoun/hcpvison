<?php
$pageTitle = 'Enquêteurs - HCPVision';
include ROOT_PATH . '/app/views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users me-2"></i>
        Gestion des Enquêteurs
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <a href="/enqueteurs/export?format=csv" class="btn btn-sm btn-success" target="_blank">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
            <a href="/enqueteurs/export?format=pdf" class="btn btn-sm btn-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtres
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="/enqueteurs" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Nom ou NINV..." 
                       value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Statut</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="Actif" <?php echo ($filters['status'] ?? '') === 'Actif' ? 'selected' : ''; ?>>Actif</option>
                    <option value="Inactif" <?php echo ($filters['status'] ?? '') === 'Inactif' ? 'selected' : ''; ?>>Inactif</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="min_surveys" class="form-label">Min. Enquêtes</label>
                <input type="number" class="form-control" id="min_surveys" name="min_surveys" 
                       min="0" placeholder="0" 
                       value="<?php echo htmlspecialchars($filters['min_surveys'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Enquêteurs</h6>
                        <h3><?php echo count($enqueteurs); ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x opacity-75"></i>
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
                        <h6 class="card-title">Enquêteurs Actifs</h6>
                        <h3><?php echo count(array_filter($enqueteurs, fn($e) => ($e['statut'] ?? 'Actif') === 'Actif')); ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-check fa-2x opacity-75"></i>
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
                        <h6 class="card-title">Total Enquêtes</h6>
                        <h3><?php echo array_sum(array_column($enqueteurs, 'nbr_enquetes')); ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
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
                        <h6 class="card-title">Taux Moy. Completion</h6>
                        <h3><?php 
                        $validRates = array_filter(array_column($enqueteurs, 'taux_completion'), fn($rate) => $rate > 0);
                        echo count($validRates) > 0 ? number_format(array_sum($validRates) / count($validRates), 1) : '0';
                        ?>%</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-percentage fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Performance des Enquêteurs
                </h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshChart('enqueteur')">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="enqueteurChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enqueteurs Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-table me-2"></i>
            Liste des Enquêteurs
        </h5>
        <span class="badge bg-secondary"><?php echo count($enqueteurs); ?> enquêteurs</span>
    </div>
    <div class="card-body">
        <?php if (!empty($enqueteurs)): ?>
            <div class="table-responsive">
                <table class="table table-hover data-table">
                    <thead>
                        <tr>
                            <th>NINV</th>
                            <th>Nom</th>
                            <th>Statut</th>
                            <th>Nb Enquêtes</th>
                            <th>Taux Completion</th>
                            <th>Durée Moyenne</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enqueteurs as $enqueteur): ?>
                            <tr>
                                <td>
                                    <code><?php echo htmlspecialchars($enqueteur['ninv']); ?></code>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($enqueteur['enq_nom']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo ($enqueteur['statut'] ?? 'Actif') === 'Actif' ? 'success' : 'secondary'; ?>">
                                        <?php echo htmlspecialchars($enqueteur['statut'] ?? 'Actif'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo number_format($enqueteur['nbr_enquetes']); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress me-2" style="width: 60px; height: 20px;">
                                            <div class="progress-bar <?php echo $enqueteur['taux_completion'] > 80 ? 'bg-success' : ($enqueteur['taux_completion'] > 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $enqueteur['taux_completion']; ?>%">
                                            </div>
                                        </div>
                                        <small><?php echo number_format($enqueteur['taux_completion'], 1); ?>%</small>
                                    </div>
                                </td>
                                <td>
                                    <?php echo $enqueteur['duree_moyenne'] > 0 ? number_format($enqueteur['duree_moyenne'], 1) . ' min' : '-'; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/enqueteurs/detail?ninv=<?php echo urlencode($enqueteur['ninv']); ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-secondary" 
                                                    onclick="editEnqueteur('<?php echo htmlspecialchars($enqueteur['ninv']); ?>')"
                                                    title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5>Aucun enquêteur trouvé</h5>
                <p class="text-muted">Aucun enquêteur ne correspond aux critères de recherche.</p>
                <a href="/enqueteurs" class="btn btn-primary">
                    <i class="fas fa-refresh"></i> Réinitialiser les filtres
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Initialize performance chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('enqueteurChart');
    if (ctx) {
        fetch('/api/enqueteurs/performance')
            .then(response => response.json())
            .then(data => {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels || [],
                        datasets: [{
                            label: 'Nombre d\'enquêtes',
                            data: data.enquetes || [],
                            backgroundColor: 'rgba(102, 126, 234, 0.8)',
                            borderColor: 'rgba(102, 126, 234, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Taux de completion (%)',
                            data: data.taux_completion || [],
                            backgroundColor: 'rgba(118, 75, 162, 0.8)',
                            borderColor: 'rgba(118, 75, 162, 1)',
                            borderWidth: 1,
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
                                    text: 'Nombre d\'enquêtes'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Taux de completion (%)'
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
            })
            .catch(error => {
                console.error('Error loading performance data:', error);
                document.getElementById('enqueteurChart').innerHTML = '<div class="text-center text-muted p-4">Erreur de chargement des données</div>';
            });
    }
});

function editEnqueteur(ninv) {
    // This would open an edit modal
    alert('Fonctionnalité d\'édition à implémenter pour l\'enquêteur: ' + ninv);
}

function refreshChart(chartType) {
    location.reload();
}
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
