<!-- Sidebar Navigation -->
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false || $_SERVER['REQUEST_URI'] === '/') ? 'active' : ''; ?>" href="/dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    Tableau de Bord
                </a>
            </li>
            
            <!-- Enquêteurs -->
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/enqueteurs') !== false ? 'active' : ''; ?>" href="/enqueteurs">
                    <i class="fas fa-users"></i>
                    Enquêteurs
                </a>
            </li>
            
            <!-- Régions -->
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/regions') !== false ? 'active' : ''; ?>" href="/regions">
                    <i class="fas fa-map-marked-alt"></i>
                    Régions
                </a>
            </li>
            
            <!-- Anomalies -->
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/anomalies') !== false ? 'active' : ''; ?>" href="/anomalies">
                    <i class="fas fa-exclamation-triangle"></i>
                    Anomalies
                    <span class="badge bg-danger ms-2">3</span>
                </a>
            </li>
            
            <!-- Rapports -->
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/rapports') !== false ? 'active' : ''; ?>" href="/rapports">
                    <i class="fas fa-file-alt"></i>
                    Rapports
                </a>
            </li>
            
            <!-- Separator -->
            <li class="nav-item mt-3 mb-2">
                <hr class="text-white-50">
            </li>
            
            <!-- Mon Compte -->
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/comptes') !== false ? 'active' : ''; ?>" href="/comptes">
                    <i class="fas fa-user-circle"></i>
                    Mon Compte
                </a>
            </li>
            
            <!-- Paramètres (Admin only) -->
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/parametres') !== false ? 'active' : ''; ?>" href="/parametres">
                    <i class="fas fa-cogs"></i>
                    Paramètres
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Separator -->
            <li class="nav-item mt-3 mb-2">
                <hr class="text-white-50">
            </li>
            
            <!-- Quick Actions -->
            <li class="nav-item mb-2">
                <small class="text-white-50 text-uppercase fw-bold ps-3">Actions Rapides</small>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="/rapports/generate?type=dashboard&format=pdf" target="_blank">
                    <i class="fas fa-download"></i>
                    Export Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="/enqueteurs/export?format=csv" target="_blank">
                    <i class="fas fa-file-csv"></i>
                    Export Enquêteurs
                </a>
            </li>
            
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="/parametres" onclick="return confirm('Effectuer une sauvegarde de la base de données ?')">
                    <i class="fas fa-database"></i>
                    Sauvegarde DB
                </a>
            </li>
            <?php endif; ?>
        </ul>
        
        <!-- System Status -->
        <div class="mt-auto pt-3">
            <div class="card bg-transparent border-light">
                <div class="card-body p-2">
                    <small class="text-white-50">
                        <div class="d-flex justify-content-between">
                            <span>Statut Système</span>
                            <span class="text-success">
                                <i class="fas fa-circle" style="font-size: 8px;"></i>
                                En ligne
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span>Base de données</span>
                            <span class="text-success">
                                <i class="fas fa-circle" style="font-size: 8px;"></i>
                                Connecté
                            </span>
                        </div>
                    </small>
                </div>
            </div>
        </div>
    </div>
</nav>
