<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-person me-2"></i>
        Détails Enquêteur: <?= htmlspecialchars($details['enq_nom']) ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/enqueteurs" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Retour
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="printDetails()">
                <i class="bi bi-printer me-1"></i>
                Imprimer
            </button>
        </div>
    </div>
</div>

<!-- Enqueteur Info Card -->
<div class="row mb-4">
    <div class="col-lg-4 mb-3">
        <div class="card">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-badge me-2"></i>
                    Informations Générales
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>NINV:</strong></div>
                    <div class="col-sm-8">
                        <span class="badge bg-primary"><?= htmlspecialchars($details['ninv']) ?></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Nom:</strong></div>
                    <div class="col-sm-8"><?= htmlspecialchars($details['enq_nom']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Statut:</strong></div>
                    <div class="col-sm-8">
                        <span class="badge bg-<?= $details['statut'] === 'Actif' ? 'success' : 'warning' ?>">
                            <?= htmlspecialchars($details['statut']) ?>
                        </span>
                    </div>
                </div>
                
                <?php if (Auth::isAdmin()): ?>
                <hr>
                <form method="POST" action="/enqueteurs/update">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="ninv" value="<?= htmlspecialchars($details['ninv']) ?>">
                    
                    <div class="mb-3">
                        <label for="statut" class="form-label">Statut</label>
                        <select class="form-select form-select-sm" name="statut" id="statut">
                            <option value="Actif" <?= $details['statut'] === 'Actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="Inactif" <?= $details['statut'] === 'Inactif' ? 'selected' : '' ?>>Inactif</option>
                            <option value="Suspendu" <?= $details['statut'] === 'Suspendu' ? 'selected' : '' ?>>Suspendu</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="commentaire" class="form-label">Commentaire</label>
                        <textarea class="form-control form-control-sm" name="commentaire" id="commentaire" rows="3"><?= htmlspecialchars($details['commentaire'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Sauvegarder
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Performance Metrics -->
    <div class="col-lg-8 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    Métriques de Performance
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <div class="p-3 bg-light rounded">
                            <i class="bi bi-clipboard-data fs-1 text-primary"></i>
                            <h4 class="mt-2 mb-0"><?= $details['nbr_enquetes'] ?? 0 ?></h4>
                            <small class="text-muted">Total Enquêtes</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="p-3 bg-light rounded">
                            <i class="bi bi-check-circle fs-1 text-success"></i>
                            <h4 class="mt-2 mb-0"><?= round($details['taux_completion'] ?? 0, 1) ?>%</h4>
                            <small class="text-muted">Taux Completion</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="p-3 bg-light rounded">
                            <i class="bi bi-clock fs-1 text-info"></i>
                            <h4 class="mt-2 mb-0"><?= round($details['duree_moyenne'] ?? 0, 1) ?></h4>
                            <small class="text-muted">Durée Moy. (min)</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="p-3 bg-light rounded">
                            <i class="bi bi-trash fs-1 text-warning"></i>
                            <h4 class="mt-2 mb-0"><?= round($details['taux_suppression'] ?? 0, 1) ?>%</h4>
                            <small class="text-muted">Taux Suppression</small>
                        </div>
                    </div>
                </div>
                
                <!-- Duration Range -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <small class="text-muted">Durée minimale:</small>
                        <strong><?= $details['duree_min'] ?? 'N/A' ?> min</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Durée maximale:</small>
                        <strong><?= $details['duree_max'] ?? 'N/A' ?> min</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Distribution -->
<div class="row mb-4">
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart me-2"></i>
                    Répartition des Statuts
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status Details Table -->
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-check me-2"></i>
                    Détail des Statuts
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Statut</th>
                                <th class="text-end">Nombre</th>
                                <th class="text-end">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = array_sum(array_column($statuts_enqueteur, 'nb'));
                            foreach ($statuts_enqueteur as $statut): 
                            ?>
                            <tr>
                                <td>
                                    <?php
                                    $badgeClass = match($statut['statut_libelle']) {
                                        'Terminé' => 'success',
                                        'EN COURS' => 'primary',
                                        'Partiel' => 'warning',
                                        'Refus' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= htmlspecialchars($statut['statut_libelle']) ?></span>
                                </td>
                                <td class="text-end"><strong><?= $statut['nb'] ?></strong></td>
                                <td class="text-end"><?= $total > 0 ? round(($statut['nb'] / $total) * 100, 1) : 0 ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Surveys -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Enquêtes Récentes (30 dernières)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Date Début</th>
                                <th>Date Fin</th>
                                <th class="text-center">Durée</th>
                                <th>Région</th>
                                <th>Province</th>
                                <th class="text-center">Statut</th>
                                <th class="text-center">Supprimé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($enquetes_recentes)): ?>
                                <?php foreach ($enquetes_recentes as $enquete): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($enquete['dd'])) ?></td>
                                    <td><?= $enquete['df'] ? date('d/m/Y H:i', strtotime($enquete['df'])) : '-' ?></td>
                                    <td class="text-center">
                                        <?= $enquete['dureeintervw'] ? $enquete['dureeintervw'] . ' min' : '-' ?>
                                    </td>
                                    <td>Région <?= htmlspecialchars($enquete['reg']) ?></td>
                                    <td><?= htmlspecialchars($enquete['pro']) ?></td>
                                    <td class="text-center">
                                        <?php
                                        $badgeClass = match($enquete['statut_libelle']) {
                                            'Terminé' => 'success',
                                            'EN COURS' => 'primary',
                                            'Partiel' => 'warning',
                                            'Refus' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= htmlspecialchars($enquete['statut_libelle']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($enquete['case_deleted'] == '1'): ?>
                                            <i class="bi bi-check-circle text-danger" title="Supprimé"></i>
                                        <?php else: ?>
                                            <i class="bi bi-x-circle text-success" title="Non supprimé"></i>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">
                                        Aucune enquête récente trouvée
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadStatusChart();
});

function loadStatusChart() {
    const statusData = <?= json_encode($statuts_enqueteur) ?>;
    
    if (statusData && statusData.length > 0) {
        const ctx = document.getElementById('statusChart').getContext('2d');
        
        const labels = statusData.map(item => item.statut_libelle);
        const data = statusData.map(item => item.nb);
        
        const colors = {
            'Terminé': '#28a745',
            'EN COURS': '#007bff',
            'Partiel': '#ffc107',
            'Refus': '#dc3545',
            'Inconnu': '#6c757d'
        };
        
        const backgroundColors = labels.map(label => colors[label] || '#6c757d');
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
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
    }
}

function printDetails() {
    window.print();
}
</script>
