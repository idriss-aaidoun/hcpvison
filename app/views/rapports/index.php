<?php
$pageTitle = 'Rapports - HCPVision';
include ROOT_PATH . '/app/views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-file-alt me-2"></i>
        Génération de Rapports
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
        </div>
    </div>
</div>

<!-- Report Types Cards -->
<div class="row mb-4">
    <?php if (!empty($reportTypes)): ?>
        <?php foreach ($reportTypes as $typeKey => $reportType): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 report-type-card" data-type="<?php echo htmlspecialchars($typeKey); ?>">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="<?php echo htmlspecialchars($reportType['icon']); ?> fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title"><?php echo htmlspecialchars($reportType['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($reportType['description']); ?></p>
                        <button class="btn btn-primary" onclick="selectReportType('<?php echo htmlspecialchars($typeKey); ?>')">
                            <i class="fas fa-plus-circle me-1"></i>
                            Générer ce rapport
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Aucun type de rapport disponible pour le moment.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Report Generation Form -->
<div class="card mb-4" id="reportForm" style="display: none;">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-cogs me-2"></i>
            Configuration du Rapport
        </h5>
    </div>
    <div class="card-body">
        <form id="generateReportForm" method="POST" action="/rapports/generate">
            <input type="hidden" id="reportType" name="type" value="">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="format" class="form-label">
                        <i class="fas fa-file-export me-1"></i>
                        Format de sortie
                    </label>
                    <select class="form-select" id="format" name="format" required>
                        <option value="pdf">PDF</option>
                        <option value="csv">CSV</option>
                        <option value="excel">Excel</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-calendar-alt me-1"></i>
                        Options
                    </label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="include_charts" name="include_charts">
                        <label class="form-check-label" for="include_charts">
                            Inclure les graphiques
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="detailed" name="detailed">
                        <label class="form-check-label" for="detailed">
                            Rapport détaillé
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Date Filters -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="date_debut" class="form-label">Date début</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut">
                </div>
                <div class="col-md-6">
                    <label for="date_fin" class="form-label">Date fin</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin">
                </div>
            </div>
            
            <!-- Additional Filters -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="enqueteur" class="form-label">Enquêteur</label>
                    <select class="form-select" id="enqueteur" name="enqueteur">
                        <option value="">Tous les enquêteurs</option>
                        <?php if (!empty($enqueteurs)): ?>
                            <?php foreach ($enqueteurs as $enqueteur): ?>
                                <option value="<?php echo htmlspecialchars($enqueteur['ninv']); ?>">
                                    <?php echo htmlspecialchars($enqueteur['enq_nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="region" class="form-label">Région</label>
                    <select class="form-select" id="region" name="region">
                        <option value="">Toutes les régions</option>
                        <?php if (!empty($regions)): ?>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?php echo htmlspecialchars($region['region']); ?>">
                                    Région <?php echo htmlspecialchars($region['region']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="Terminé">Terminé</option>
                        <option value="EN COURS">En cours</option>
                        <option value="Partiel">Partiel</option>
                        <option value="Refus">Refus</option>
                    </select>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="cancelReport()">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>
                <button type="submit" class="btn btn-primary" id="generateBtn">
                    <i class="fas fa-download me-1"></i>
                    Générer le rapport
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Recent Reports -->
<?php if (!empty($recentReports)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-history me-2"></i>
            Rapports Récents
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Format</th>
                        <th>Date génération</th>
                        <th>Utilisateur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentReports as $report): ?>
                        <tr>
                            <td>
                                <i class="<?php echo htmlspecialchars($report['icon'] ?? 'fas fa-file'); ?> me-2"></i>
                                <?php echo htmlspecialchars($report['name']); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $report['format'] === 'pdf' ? 'danger' : ($report['format'] === 'csv' ? 'success' : 'info'); ?>">
                                    <?php echo strtoupper($report['format']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($report['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($report['user_name']); ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($report['download_url']); ?>" 
                                   class="btn btn-sm btn-outline-primary" 
                                   target="_blank">
                                    <i class="fas fa-download"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Reports -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-bolt me-2"></i>
            Rapports Rapides
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="list-group">
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Tableau de bord aujourd'hui</h6>
                            <p class="mb-1 text-muted">KPI et statistiques du jour</p>
                        </div>
                        <a href="/rapports/generate?type=dashboard&format=pdf&date_debut=<?php echo date('Y-m-d'); ?>&date_fin=<?php echo date('Y-m-d'); ?>" 
                           class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Performance hebdomadaire</h6>
                            <p class="mb-1 text-muted">Rapport des 7 derniers jours</p>
                        </div>
                        <a href="/rapports/generate?type=enqueteurs&format=pdf&date_debut=<?php echo date('Y-m-d', strtotime('-7 days')); ?>" 
                           class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Rapport mensuel</h6>
                            <p class="mb-1 text-muted">Synthèse du mois en cours</p>
                        </div>
                        <a href="/rapports/generate?type=regions&format=pdf&date_debut=<?php echo date('Y-m-01'); ?>" 
                           class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="list-group">
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Export des anomalies</h6>
                            <p class="mb-1 text-muted">Liste complète des anomalies</p>
                        </div>
                        <a href="/anomalies/export?format=csv" 
                           class="btn btn-sm btn-success" target="_blank">
                            <i class="fas fa-file-csv"></i>
                        </a>
                    </div>
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Liste des enquêteurs</h6>
                            <p class="mb-1 text-muted">Export complet avec statistiques</p>
                        </div>
                        <a href="/enqueteurs/export?format=csv" 
                           class="btn btn-sm btn-success" target="_blank">
                            <i class="fas fa-file-csv"></i>
                        </a>
                    </div>
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Données par région</h6>
                            <p class="mb-1 text-muted">Export des données régionales</p>
                        </div>
                        <a href="/regions/export?format=csv" 
                           class="btn btn-sm btn-success" target="_blank">
                            <i class="fas fa-file-csv"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
.report-type-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.report-type-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.report-type-card.selected {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

#reportForm {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.list-group-item-action:hover {
    background-color: #f8f9fa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    const lastMonth = new Date();
    lastMonth.setMonth(lastMonth.getMonth() - 1);
    const lastMonthStr = lastMonth.toISOString().split('T')[0];
    
    document.getElementById('date_fin').value = today;
    document.getElementById('date_debut').value = lastMonthStr;
});

function selectReportType(type) {
    // Remove selection from all cards
    document.querySelectorAll('.report-type-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selection to clicked card
    const selectedCard = document.querySelector(`[data-type="${type}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }
    
    // Set the report type and show form
    document.getElementById('reportType').value = type;
    document.getElementById('reportForm').style.display = 'block';
    
    // Scroll to form
    document.getElementById('reportForm').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
    });
    
    // Update form fields based on report type
    updateFormForReportType(type);
}

function updateFormForReportType(type) {
    const includeChartsCheckbox = document.getElementById('include_charts');
    const detailedCheckbox = document.getElementById('detailed');
    const formatSelect = document.getElementById('format');
    
    // Set defaults based on report type
    switch (type) {
        case 'dashboard':
            includeChartsCheckbox.checked = true;
            detailedCheckbox.checked = true;
            formatSelect.value = 'pdf';
            break;
        case 'enqueteurs':
        case 'regions':
            includeChartsCheckbox.checked = true;
            detailedCheckbox.checked = false;
            formatSelect.value = 'pdf';
            break;
        case 'anomalies':
            includeChartsCheckbox.checked = false;
            detailedCheckbox.checked = true;
            formatSelect.value = 'csv';
            break;
        case 'collecte':
            includeChartsCheckbox.checked = true;
            detailedCheckbox.checked = false;
            formatSelect.value = 'pdf';
            break;
        case 'qualite':
            includeChartsCheckbox.checked = false;
            detailedCheckbox.checked = true;
            formatSelect.value = 'csv';
            break;
    }
}

function cancelReport() {
    document.getElementById('reportForm').style.display = 'none';
    
    // Remove selection from all cards
    document.querySelectorAll('.report-type-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Reset form
    document.getElementById('generateReportForm').reset();
}

// Handle form submission
document.getElementById('generateReportForm').addEventListener('submit', function(e) {
    const generateBtn = document.getElementById('generateBtn');
    const originalText = generateBtn.innerHTML;
    
    // Show loading state
    generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Génération en cours...';
    generateBtn.disabled = true;
    
    // Reset button after a delay (the form will be submitted to a new window)
    setTimeout(function() {
        generateBtn.innerHTML = originalText;
        generateBtn.disabled = false;
    }, 3000);
});

// Handle quick report clicks
document.addEventListener('click', function(e) {
    if (e.target.closest('a[href*="/rapports/generate"]')) {
        const link = e.target.closest('a');
        const originalText = link.innerHTML;
        
        // Show loading state
        link.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        // Reset after delay
        setTimeout(function() {
            link.innerHTML = originalText;
        }, 2000);
    }
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
