<?php
$pageTitle = 'Anomalies - HCPVision';
include ROOT_PATH . '/app/views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Détection d'Anomalies
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <a href="/anomalies/export?format=csv" class="btn btn-sm btn-success" target="_blank">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
            <a href="/anomalies/export?format=pdf" class="btn btn-sm btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>
</div>

<!-- Quality Metrics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Anomalies</h6>
                        <h3><?php echo number_format($qualityMetrics['total_anomalies'] ?? 0); ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                        <h6 class="card-title">Taux d'Anomalies</h6>
                        <h3><?php echo number_format($qualityMetrics['anomaly_rate'] ?? 0, 1); ?>%</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-percentage fa-2x opacity-75"></i>
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
                        <h6 class="card-title">Taux Suppression</h6>
                        <h3><?php echo number_format($qualityMetrics['suppression_rate'] ?? 0, 1); ?>%</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-trash-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Enquêtes</h6>
                        <h3><?php echo number_format($qualityMetrics['total_surveys'] ?? 0); ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Anomaly Types Distribution -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Types d'Anomalies
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center p-3 border-end">
                        <h4 class="text-danger"><?php echo number_format($anomaliesByType['date_inconsistencies'] ?? 0); ?></h4>
                        <small class="text-muted">Dates Incohérentes</small>
                    </div>
                    <div class="col-6 text-center p-3">
                        <h4 class="text-warning"><?php echo number_format($anomaliesByType['null_durations'] ?? 0); ?></h4>
                        <small class="text-muted">Durées Manquantes</small>
                    </div>
                    <div class="col-12 text-center p-3 border-top">
                        <h4 class="text-info"><?php echo number_format($anomaliesByType['extreme_durations'] ?? 0); ?></h4>
                        <small class="text-muted">Durées Extrêmes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Anomalies by Enqueteur -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    Top Enquêteurs avec Anomalies
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($anomaliesByEnqueteur)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Enquêteur</th>
                                    <th>Anomalies</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($anomaliesByEnqueteur, 0, 8) as $enqueteur): ?>
                                    <tr>
                                        <td>
                                            <a href="/enqueteurs/detail?ninv=<?php echo urlencode($enqueteur['ninv']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($enqueteur['enq_nom']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger"><?php echo number_format($enqueteur['anomalies_count']); ?></span>
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
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune donnée d'enquêteur disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Data Quality Details -->
<?php if (isset($qualityMetrics['null_analysis'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-database me-2"></i>
                    Analyse des Valeurs Nulles
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center p-3">
                        <h4 class="text-primary"><?php echo number_format($qualityMetrics['null_analysis']['total'] ?? 0); ?></h4>
                        <small class="text-muted">Total Enquêtes</small>
                    </div>
                    <div class="col-md-2 text-center p-3">
                        <h4 class="text-warning"><?php echo number_format($qualityMetrics['null_analysis']['null_dd'] ?? 0); ?></h4>
                        <small class="text-muted">Date Début Null</small>
                    </div>
                    <div class="col-md-2 text-center p-3">
                        <h4 class="text-danger"><?php echo number_format($qualityMetrics['null_analysis']['null_df'] ?? 0); ?></h4>
                        <small class="text-muted">Date Fin Null</small>
                    </div>
                    <div class="col-md-2 text-center p-3">
                        <h4 class="text-info"><?php echo number_format($qualityMetrics['null_analysis']['null_duree'] ?? 0); ?></h4>
                        <small class="text-muted">Durée Null</small>
                    </div>
                    <div class="col-md-2 text-center p-3">
                        <h4 class="text-secondary"><?php echo number_format($qualityMetrics['null_analysis']['null_enq'] ?? 0); ?></h4>
                        <small class="text-muted">Enquêteur Null</small>
                    </div>
                    <div class="col-md-2 text-center p-3">
                        <h4 class="text-success">
                            <?php 
                            $total = $qualityMetrics['null_analysis']['total'] ?? 1;
                            $nulls = ($qualityMetrics['null_analysis']['null_dd'] ?? 0) + 
                                   ($qualityMetrics['null_analysis']['null_df'] ?? 0) + 
                                   ($qualityMetrics['null_analysis']['null_duree'] ?? 0) + 
                                   ($qualityMetrics['null_analysis']['null_enq'] ?? 0);
                            echo number_format((($total * 4 - $nulls) / ($total * 4)) * 100, 1);
                            ?>%
                        </h4>
                        <small class="text-muted">Complétude</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Date Anomalies -->
<?php if (!empty($dateAnomalies)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-times me-2"></i>
                    Anomalies de Dates
                    <span class="badge bg-light text-dark"><?php echo count($dateAnomalies); ?></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Enquêteur</th>
                                <th>Date Début</th>
                                <th>Date Fin</th>
                                <th>Problème</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($dateAnomalies, 0, 10) as $anomalie): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($anomalie['c99-id']); ?></code></td>
                                    <td>
                                        <a href="/enqueteurs/detail?ninv=<?php echo urlencode($anomalie['ninv']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($anomalie['enq_nom']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="<?php echo empty($anomalie['dd']) ? 'text-danger' : ''; ?>">
                                            <?php echo $anomalie['dd'] ? date('d/m/Y H:i', strtotime($anomalie['dd'])) : 'NULL'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="<?php echo empty($anomalie['df']) ? 'text-danger' : ''; ?>">
                                            <?php echo $anomalie['df'] ? date('d/m/Y H:i', strtotime($anomalie['df'])) : 'NULL'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        if (empty($anomalie['df'])) {
                                            echo '<span class="badge bg-danger">Date fin manquante</span>';
                                        } elseif (!empty($anomalie['dd']) && !empty($anomalie['df']) && $anomalie['df'] <= $anomalie['dd']) {
                                            echo '<span class="badge bg-warning">Date fin ≤ date début</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Autre</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-success" 
                                                        onclick="fixAnomaly('<?php echo $anomalie['c99-id']; ?>', 'mark_resolved')"
                                                        title="Marquer comme résolu">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="fixAnomaly('<?php echo $anomalie['c99-id']; ?>', 'delete')"
                                                        title="Supprimer"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette enquête ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (count($dateAnomalies) > 10): ?>
                    <div class="text-center mt-3">
                        <p class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Affichage des 10 premières anomalies sur <?php echo count($dateAnomalies); ?> total.
                            <a href="/anomalies/export?format=csv&type=date" class="text-decoration-none">Exporter tout</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Duration Anomalies -->
<?php if (!empty($durationAnomalies)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Anomalies de Durée
                    <span class="badge bg-dark"><?php echo count($durationAnomalies); ?></span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Enquêteur</th>
                                <th>Date</th>
                                <th>Durée</th>
                                <th>Problème</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($durationAnomalies, 0, 10) as $anomalie): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($anomalie['c99-id']); ?></code></td>
                                    <td>
                                        <a href="/enqueteurs/detail?ninv=<?php echo urlencode($anomalie['ninv']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($anomalie['enq_nom']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo $anomalie['dd'] ? date('d/m/Y H:i', strtotime($anomalie['dd'])) : '-'; ?>
                                    </td>
                                    <td>
                                        <span class="<?php echo empty($anomalie['dureeintervw']) ? 'text-danger' : 'text-warning'; ?>">
                                            <?php echo $anomalie['dureeintervw'] ?? 'NULL'; ?>
                                            <?php if (!empty($anomalie['dureeintervw'])): ?> min<?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        if (empty($anomalie['dureeintervw'])) {
                                            echo '<span class="badge bg-danger">Durée manquante</span>';
                                        } elseif ($anomalie['dureeintervw'] >= 180) {
                                            echo '<span class="badge bg-warning">Durée excessive (≥3h)</span>';
                                        } elseif ($anomalie['dureeintervw'] > 9999) {
                                            echo '<span class="badge bg-info">Valeur aberrante</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Autre</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-success" 
                                                        onclick="fixAnomaly('<?php echo $anomalie['c99-id']; ?>', 'mark_resolved')"
                                                        title="Marquer comme résolu">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="fixAnomaly('<?php echo $anomalie['c99-id']; ?>', 'delete')"
                                                        title="Supprimer"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette enquête ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (count($durationAnomalies) > 10): ?>
                    <div class="text-center mt-3">
                        <p class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Affichage des 10 premières anomalies sur <?php echo count($durationAnomalies); ?> total.
                            <a href="/anomalies/export?format=csv&type=duration" class="text-decoration-none">Exporter tout</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- All Anomalies -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>
                    Toutes les Anomalies
                </h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary active" data-filter="all">Toutes</button>
                    <button class="btn btn-sm btn-outline-secondary" data-filter="date">Dates</button>
                    <button class="btn btn-sm btn-outline-secondary" data-filter="duration">Durées</button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($anomalies)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="anomaliesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NINV</th>
                                    <th>Enquêteur</th>
                                    <th>Date Début</th>
                                    <th>Date Fin</th>
                                    <th>Durée</th>
                                    <th>Région</th>
                                    <th>Statut</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($anomalies, 0, 50) as $anomalie): ?>
                                    <tr data-anomaly-types="<?php 
                                    $types = [];
                                    if (empty($anomalie['df']) || (!empty($anomalie['dd']) && !empty($anomalie['df']) && $anomalie['df'] <= $anomalie['dd'])) {
                                        $types[] = 'date';
                                    }
                                    if (empty($anomalie['dureeintervw']) || $anomalie['dureeintervw'] >= 180 || $anomalie['dureeintervw'] > 9999) {
                                        $types[] = 'duration';
                                    }
                                    echo implode(',', $types);
                                    ?>">
                                        <td><code><?php echo htmlspecialchars($anomalie['c99-id']); ?></code></td>
                                        <td><code><?php echo htmlspecialchars($anomalie['ninv']); ?></code></td>
                                        <td>
                                            <a href="/enqueteurs/detail?ninv=<?php echo urlencode($anomalie['ninv']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($anomalie['enq_nom']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo $anomalie['dd'] ? date('d/m H:i', strtotime($anomalie['dd'])) : '-'; ?></td>
                                        <td><?php echo $anomalie['df'] ? date('d/m H:i', strtotime($anomalie['df'])) : '-'; ?></td>
                                        <td><?php echo $anomalie['dureeintervw'] ?? '-'; ?></td>
                                        <td><span class="badge bg-secondary">Région <?php echo htmlspecialchars($anomalie['enq_reg']); ?></span></td>
                                        <td>
                                            <span class="badge bg-<?php echo $anomalie['statut'] == '1' ? 'success' : 'secondary'; ?>">
                                                <?php echo $anomalie['statut'] == '1' ? 'Terminé' : 'Non terminé'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php
                                                if (empty($anomalie['df']) || (!empty($anomalie['dd']) && !empty($anomalie['df']) && $anomalie['df'] <= $anomalie['dd'])) {
                                                    echo '<span class="badge bg-danger" style="font-size: 0.7em;">Date</span>';
                                                }
                                                if (empty($anomalie['dureeintervw'])) {
                                                    echo '<span class="badge bg-warning" style="font-size: 0.7em;">Durée NULL</span>';
                                                } elseif ($anomalie['dureeintervw'] >= 180) {
                                                    echo '<span class="badge bg-info" style="font-size: 0.7em;">Durée +3h</span>';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($anomalies) > 50): ?>
                        <div class="text-center mt-3">
                            <p class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Affichage des 50 premières anomalies sur <?php echo count($anomalies); ?> total.
                                <a href="/anomalies/export?format=csv" class="text-decoration-none">Exporter toutes les anomalies</a>
                            </p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>Aucune anomalie détectée</h5>
                        <p class="text-muted">Félicitations ! Aucune anomalie n'a été détectée dans les données.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeAnomaliesFilters();
});

function initializeAnomaliesFilters() {
    const filterButtons = document.querySelectorAll('[data-filter]');
    const tableRows = document.querySelectorAll('#anomaliesTable tbody tr');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter table rows
            tableRows.forEach(row => {
                const anomalyTypes = row.getAttribute('data-anomaly-types') || '';
                
                if (filter === 'all') {
                    row.style.display = '';
                } else {
                    const shouldShow = anomalyTypes.includes(filter);
                    row.style.display = shouldShow ? '' : 'none';
                }
            });
        });
    });
}

function fixAnomaly(anomalyId, action) {
    if (action === 'delete' && !confirm('Êtes-vous sûr de vouloir supprimer cette enquête ?')) {
        return;
    }
    
    fetch('/anomalies/fix', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${anomalyId}&action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la correction de l\'anomalie');
    });
}
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
