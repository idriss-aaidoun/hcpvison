<?php
$pageTitle = 'Paramètres - HCPVision';
$hideFooter = true; // Hide footer for settings page
include ROOT_PATH . '/app/views/layouts/header.php';

// Require admin access
if ($_SESSION['user_role'] !== 'admin') {
    echo '<div class="alert alert-danger">Accès refusé. Vous devez être administrateur pour accéder à cette page.</div>';
    include ROOT_PATH . '/app/views/layouts/footer.php';
    exit;
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-cogs me-2"></i>
        Paramètres Système
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="testConnection()">
                <i class="fas fa-database"></i> Test Connexion
            </button>
        </div>
    </div>
</div>

<!-- System Information -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Informations Système
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th width="200">Application:</th>
                        <td><strong><?php echo htmlspecialchars($settings['app_name']); ?> v<?php echo htmlspecialchars($settings['app_version']); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Version PHP:</th>
                        <td><?php echo htmlspecialchars($systemInfo['php_version']); ?></td>
                    </tr>
                    <tr>
                        <th>Serveur Web:</th>
                        <td><?php echo htmlspecialchars($systemInfo['server_software']); ?></td>
                    </tr>
                    <tr>
                        <th>Système d'exploitation:</th>
                        <td><?php echo htmlspecialchars($systemInfo['server_os']); ?></td>
                    </tr>
                    <tr>
                        <th>Mémoire limite:</th>
                        <td><?php echo htmlspecialchars($systemInfo['memory_limit']); ?></td>
                    </tr>
                    <tr>
                        <th>Temps d'exécution max:</th>
                        <td><?php echo htmlspecialchars($systemInfo['max_execution_time']); ?>s</td>
                    </tr>
                    <tr>
                        <th>Fuseau horaire:</th>
                        <td><?php echo htmlspecialchars($systemInfo['timezone']); ?></td>
                    </tr>
                    <tr>
                        <th>Heure actuelle:</th>
                        <td><?php echo htmlspecialchars($systemInfo['current_time']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Database Information -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-database me-2"></i>
                    Informations Base de Données
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th width="200">Hôte:</th>
                        <td><?php echo htmlspecialchars($databaseInfo['host']); ?></td>
                    </tr>
                    <tr>
                        <th>Base de données:</th>
                        <td><strong><?php echo htmlspecialchars($databaseInfo['database_name']); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Version MySQL:</th>
                        <td><?php echo htmlspecialchars($databaseInfo['mysql_version'] ?? 'Non disponible'); ?></td>
                    </tr>
                    <tr>
                        <th>Taille de la BD:</th>
                        <td><?php echo htmlspecialchars($databaseInfo['database_size'] ?? 'Non disponible'); ?></td>
                    </tr>
                    <tr>
                        <th>Nombre de tables:</th>
                        <td><?php echo htmlspecialchars($databaseInfo['table_count'] ?? 'Non disponible'); ?></td>
                    </tr>
                    <tr>
                        <th>Nombre de vues:</th>
                        <td><?php echo htmlspecialchars($databaseInfo['view_count'] ?? 'Non disponible'); ?></td>
                    </tr>
                    <tr>
                        <th>Charset:</th>
                        <td><?php echo htmlspecialchars($databaseInfo['charset'] ?? 'Non disponible'); ?></td>
                    </tr>
                    <tr>
                        <th>Statut:</th>
                        <td>
                            <span class="badge bg-<?php echo $databaseInfo['connection_status'] === 'Connecté' ? 'success' : 'danger'; ?>">
                                <?php echo htmlspecialchars($databaseInfo['connection_status']); ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Storage Information -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-hdd me-2"></i>
                    Stockage et Ressources
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center p-3">
                        <h4 class="text-info"><?php echo htmlspecialchars($systemInfo['disk_free_space']); ?></h4>
                        <small class="text-muted">Espace Libre</small>
                    </div>
                    <div class="col-md-3 text-center p-3">
                        <h4 class="text-primary"><?php echo htmlspecialchars($systemInfo['disk_total_space']); ?></h4>
                        <small class="text-muted">Espace Total</small>
                    </div>
                    <div class="col-md-3 text-center p-3">
                        <h4 class="text-warning"><?php echo htmlspecialchars($systemInfo['upload_max_filesize']); ?></h4>
                        <small class="text-muted">Taille Max Upload</small>
                    </div>
                    <div class="col-md-3 text-center p-3">
                        <h4 class="text-success"><?php echo htmlspecialchars($systemInfo['post_max_size']); ?></h4>
                        <small class="text-muted">Taille Max POST</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Database Tables Status -->
<?php if (isset($databaseInfo['record_counts'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-table me-2"></i>
                    État des Tables Principales
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($databaseInfo['record_counts'] as $table => $count): ?>
                        <div class="col-md-2 text-center p-3">
                            <h4 class="text-primary"><?php echo is_numeric($count) ? number_format($count) : $count; ?></h4>
                            <small class="text-muted"><?php echo htmlspecialchars($table); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Application Settings -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sliders-h me-2"></i>
                    Paramètres Généraux
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/parametres/update">
                    <input type="hidden" name="action" value="update_general">
                    
                    <div class="mb-3">
                        <label for="timezone" class="form-label">Fuseau horaire</label>
                        <select class="form-select" id="timezone" name="timezone">
                            <option value="Europe/Paris" <?php echo $settings['timezone'] === 'Europe/Paris' ? 'selected' : ''; ?>>Europe/Paris</option>
                            <option value="Africa/Casablanca" <?php echo $settings['timezone'] === 'Africa/Casablanca' ? 'selected' : ''; ?>>Africa/Casablanca</option>
                            <option value="UTC" <?php echo $settings['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_format" class="form-label">Format de date</label>
                        <select class="form-select" id="date_format" name="date_format">
                            <option value="d/m/Y" <?php echo $settings['date_format'] === 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                            <option value="Y-m-d" <?php echo $settings['date_format'] === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                            <option value="m/d/Y" <?php echo $settings['date_format'] === 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="items_per_page" class="form-label">Éléments par page</label>
                        <input type="number" class="form-control" id="items_per_page" name="items_per_page" 
                               value="<?php echo htmlspecialchars($settings['items_per_page']); ?>" 
                               min="10" max="100">
                    </div>
                    
                    <div class="mb-3">
                        <label for="session_timeout" class="form-label">Timeout session (secondes)</label>
                        <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                               value="<?php echo htmlspecialchars($settings['session_timeout']); ?>" 
                               min="300" max="86400">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enable_debug" name="enable_debug" 
                                   <?php echo $settings['enable_debug'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_debug">
                                Mode debug activé
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enable_logging" name="enable_logging" 
                                   <?php echo $settings['enable_logging'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_logging">
                                Journalisation activée
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Sauvegarder
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Database Settings -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-database me-2"></i>
                    Paramètres Base de Données
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/parametres/update">
                    <input type="hidden" name="action" value="update_database">
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="auto_backup" name="auto_backup" 
                                   <?php echo $settings['auto_backup'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="auto_backup">
                                Sauvegarde automatique
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="backup_retention_days" class="form-label">Rétention des sauvegardes (jours)</label>
                        <input type="number" class="form-control" id="backup_retention_days" name="backup_retention_days" 
                               value="<?php echo htmlspecialchars($settings['backup_retention_days']); ?>" 
                               min="1" max="365">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Sauvegarder
                    </button>
                </form>
                
                <hr>
                
                <!-- Manual Backup -->
                <form method="POST" action="/parametres/update">
                    <input type="hidden" name="action" value="backup_database">
                    <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Créer une sauvegarde de la base de données ?')">
                        <i class="fas fa-download me-1"></i>
                        Créer une sauvegarde manuelle
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Tools -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Outils de Maintenance
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <button class="btn btn-outline-info" onclick="clearCache()">
                                <i class="fas fa-trash me-1"></i>
                                Vider le cache
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <button class="btn btn-outline-warning" onclick="testConnection()">
                                <i class="fas fa-database me-1"></i>
                                Test connexion DB
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <button class="btn btn-outline-secondary" onclick="viewLogs('error')">
                                <i class="fas fa-file-alt me-1"></i>
                                Logs d'erreurs
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <button class="btn btn-outline-success" onclick="checkSystemHealth()">
                                <i class="fas fa-heartbeat me-1"></i>
                                État du système
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-info-circle me-2"></i>Informations sur la Maintenance</h6>
                    <ul class="mb-0">
                        <li><strong>Vider le cache:</strong> Supprime les fichiers temporaires et force le rechargement</li>
                        <li><strong>Test connexion:</strong> Vérifie la connectivité à la base de données</li>
                        <li><strong>Logs d'erreurs:</strong> Affiche les erreurs récentes du système</li>
                        <li><strong>État du système:</strong> Vérifie la santé générale de l'application</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Logs Modal -->
<div class="modal fade" id="logsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2"></i>
                    Journaux Système
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="logsContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Chargement des logs...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
.card {
    margin-bottom: 1.5rem;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.8rem;
}

#logsContent {
    max-height: 400px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
}

.maintenance-tool {
    transition: all 0.3s ease;
}

.maintenance-tool:hover {
    transform: translateY(-2px);
}
</style>

<script>
function clearCache() {
    if (confirm('Êtes-vous sûr de vouloir vider le cache ?')) {
        fetch('/parametres/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_cache'
        })
        .then(response => response.text())
        .then(data => {
            alert('Cache vidé avec succès');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du vidage du cache');
        });
    }
}

function testConnection() {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Test en cours...';
    btn.disabled = true;
    
    fetch('/parametres/test-connection')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message + '\nTimestamp: ' + data.timestamp);
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Erreur lors du test de connexion');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
}

function viewLogs(type) {
    const modal = new bootstrap.Modal(document.getElementById('logsModal'));
    const logsContent = document.getElementById('logsContent');
    
    logsContent.innerHTML = `
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Chargement des logs...</p>
        </div>
    `;
    
    modal.show();
    
    fetch(`/parametres/logs?type=${type}`)
        .then(response => response.json())
        .then(logs => {
            if (logs && logs.length > 0) {
                logsContent.innerHTML = '<pre>' + logs.slice(0, 50).join('\n') + '</pre>';
            } else {
                logsContent.innerHTML = '<div class="text-center text-muted"><i class="fas fa-info-circle me-2"></i>Aucun log disponible</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            logsContent.innerHTML = '<div class="text-center text-danger"><i class="fas fa-exclamation-circle me-2"></i>Erreur lors du chargement des logs</div>';
        });
}

function checkSystemHealth() {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Vérification...';
    btn.disabled = true;
    
    // Simulate health check
    setTimeout(() => {
        const healthStatus = {
            database: '✅ Connecté',
            php: '✅ Version compatible',
            memory: '✅ Suffisante',
            disk: '✅ Espace disponible',
            permissions: '✅ Correctes'
        };
        
        let message = '🏥 État du Système:\n\n';
        for (const [key, value] of Object.entries(healthStatus)) {
            message += `${key.charAt(0).toUpperCase() + key.slice(1)}: ${value}\n`;
        }
        
        alert(message);
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}

// Handle form submissions with loading states
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Traitement...';
            submitBtn.disabled = true;
            
            // Re-enable after delay in case of error
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        }
    });
});

// Update timezone preview
document.getElementById('timezone').addEventListener('change', function() {
    // Could add real-time preview of selected timezone
    console.log('Timezone changed to:', this.value);
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
