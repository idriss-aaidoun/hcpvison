<?php
$pageTitle = 'Détails Enquêteur - ' . htmlspecialchars($enqueteur['enq_nom'] ?? 'Inconnu') . ' - HCPVision';
include ROOT_PATH . '/app/views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/enqueteurs">Enquêteurs</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($enqueteur['enq_nom'] ?? 'Détails'); ?></li>
            </ol>
        </nav>
        <h1 class="h2">
            <i class="fas fa-user me-2"></i>
            <?php echo htmlspecialchars($enqueteur['enq_nom'] ?? 'Enquêteur'); ?>
            <small class="text-muted">(<?php echo htmlspecialchars($enqueteur['ninv'] ?? ''); ?>)</small>
        </h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/enqueteurs" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
        </div>
    </div>
</div>

<!-- Enqueteur Info Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-id-card me-2"></i>
                    Informations Enquêteur
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">NINV:</th>
                                <td><code><?php echo htmlspecialchars($enqueteur['ninv'] ?? ''); ?></code></td>
                            </tr>
                            <tr>
                                <th>Nom:</th>
                                <td><strong><?php echo htmlspecialchars($enqueteur['enq_nom'] ?? ''); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Statut:</th>
                                <td>
                                    <span class="badge bg-<?php echo ($enqueteur['statut'] ?? 'Actif') === 'Actif' ? 'success' : 'secondary'; ?>">
                                        <?php echo htmlspecialchars($enqueteur['statut'] ?? 'Actif'); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Total Enquêtes:</th>
                                <td><span class="badge bg-primary"><?php echo number_format($enqueteur['nbr_enquetes'] ?? 0); ?></span></td>
                            </tr>
                            <tr>
                                <th>Taux Completion:</th>
                                <td>
                                    <div class="progress" style="height: 20px; width: 200px;">
                                        <div class="progress-bar <?php echo ($enqueteur['taux_completion'] ?? 0) > 80 ? 'bg-success' : (($enqueteur['taux_completion'] ?? 0) > 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo $enqueteur['taux_completion'] ?? 0; ?>%">
                                            <?php echo number_format($enqueteur['taux_completion'] ?? 0, 1); ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>Durée Moyenne:</th>
                                <td><?php echo ($enqueteur['duree_moyenne'] ?? 0) > 0 ? number_format($enqueteur['duree_moyenne'], 1) . ' min' : 'N/A'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <hr>
                <form method="POST" action="/enqueteurs/detail?ninv=<?php echo urlencode($enqueteur['ninv']); ?>">
                    <input type="hidden" name="update_comment" value="1">
                    <div class="row">
                        <div class="col-md-10">
                            <label for="commentaire" class="form-label">Commentaire (Admin uniquement)</label>
                            <textarea class="form-control" id="commentaire" name="commentaire" rows="2" 
                                      placeholder="Ajouter un commentaire..."><?php echo htmlspecialchars($enqueteur['commentaire'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Sauvegarder
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($enqueteur['nbr_enquetes'] ?? 0); ?></h4>
                <p class="mb-0">Total Enquêtes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($enqueteur['taux_completion'] ?? 0, 1); ?>%</h4>
                <p class="mb-0">Taux Completion</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4><?php echo ($enqueteur['duree_moyenne'] ?? 0) > 0 ? number_format($enqueteur['duree_moyenne'], 1) : '0'; ?></h4>
                <p class="mb-0">Durée Moy. (min)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-<?php echo ($enqueteur['taux_suppression'] ?? 0) > 10 ? 'danger' : 'secondary'; ?> text-white">
            <div class="card-body text-center">
                <h4><?php echo number_format($enqueteur['taux_suppression'] ?? 0, 1); ?>%</h4>
                <p class="mb-0">Taux Suppression</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Status Breakdown -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Répartition des Statuts
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($statusBreakdown)): ?>
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
    
    <!-- Regional Distribution -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-map-marked-alt me-2"></i>
                    Répartition Géographique
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($regionalDistribution)): ?>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="regionalChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune donnée géographique disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Surveys by Date -->
<?php if (!empty($surveysByDate)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Évolution des Enquêtes par Date
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="dateChart"></canvas>
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
                                    <th>Date Début</th>
                                    <th>Date Fin</th>
                                    <th>Durée</th>
                                    <th>Région</th>
                                    <th>Province</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentSurveys, 0, 20) as $survey): ?>
                                    <tr>
                                        <td><?php echo $survey['dd'] ? date('d/m/Y H:i', strtotime($survey['dd'])) : '-'; ?></td>
                                        <td><?php echo $survey['df'] ? date('d/m/Y H:i', strtotime($survey['df'])) : '-'; ?></td>
                                        <td><?php echo ($survey['dureeintervw'] ?? 0) > 0 ? $survey['dureeintervw'] . ' min' : '-'; ?></td>
                                        <td><span class="badge bg-secondary">Région <?php echo htmlspecialchars($survey['reg'] ?? ''); ?></span></td>
                                        <td><?php echo htmlspecialchars($survey['pro'] ?? '-'); ?></td>
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
                        <p class="text-muted">Cet enquêteur n'a pas encore d'enquêtes enregistrées.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Anomalies -->
<?php if (!empty($anomalies)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Anomalies Détectées
                    <span class="badge bg-danger"><?php echo count($anomalies); ?></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type d'Anomalie</th>
                                <th>Durée</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($anomalies as $anomalie): ?>
                                <tr>
                                    <td><?php echo $anomalie['dd'] ? date('d/m/Y H:i', strtotime($anomalie['dd'])) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $typeAnomalie = [];
                                        if (empty($anomalie['df']) || $anomalie['df'] <= $anomalie['dd']) {
                                            $typeAnomalie[] = '<span class="badge bg-danger">Date incohérente</span>';
                                        }
                                        if (empty($anomalie['dureeintervw'])) {
                                            $typeAnomalie[] = '<span class="badge bg-warning">Durée manquante</span>';
                                        }
                                        if (!empty($anomalie['dureeintervw']) && $anomalie['dureeintervw'] >= 180) {
                                            $typeAnomalie[] = '<span class="badge bg-info">Durée excessive</span>';
                                        }
                                        echo implode(' ', $typeAnomalie);
                                        ?>
                                    </td>
                                    <td><?php echo ($anomalie['dureeintervw'] ?? 0) > 0 ? $anomalie['dureeintervw'] . ' min' : '-'; ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($anomalie['statut'] ?? ''); ?></span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Breakdown Chart
    <?php if (!empty($statusBreakdown)): ?>
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusData = <?php echo json_encode($statusBreakdown); ?>;
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.statut_libelle),
                datasets: [{
                    data: statusData.map(item => item.nb),
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
    
    // Regional Distribution Chart
    <?php if (!empty($regionalDistribution)): ?>
    const regionalCtx = document.getElementById('regionalChart');
    if (regionalCtx) {
        const regionalData = <?php echo json_encode($regionalDistribution); ?>;
        
        new Chart(regionalCtx, {
            type: 'bar',
            data: {
                labels: regionalData.map(item => 'Région ' + item.region),
                datasets: [{
                    label: 'Nombre d\'enquêtes',
                    data: regionalData.map(item => item.count),
                    backgroundColor: 'rgba(102, 126, 234, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    <?php endif; ?>
    
    // Surveys by Date Chart
    <?php if (!empty($surveysByDate)): ?>
    const dateCtx = document.getElementById('dateChart');
    if (dateCtx) {
        const dateData = <?php echo json_encode($surveysByDate); ?>;
        
        new Chart(dateCtx, {
            type: 'line',
            data: {
                labels: dateData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('fr-FR');
                }),
                datasets: [{
                    label: 'Enquêtes par jour',
                    data: dateData.map(item => item.count),
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
    }
    <?php endif; ?>
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
