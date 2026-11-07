<?php
$pageTitle = 'Connexion - HCPVision';
$hideNavigation = true;
include ROOT_PATH . '/app/views/layouts/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="text-center mb-4">
            <h1 class="h3 mb-3 fw-normal">
                <i class="fas fa-chart-bar text-primary me-2"></i>
                HCPVision
            </h1>
            <p class="text-muted">Système de Visualisation des Données d'Enquêtes</p>
        </div>
        
        <?php if (isset($error) && !empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/login">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="fas fa-user me-2"></i>
                    Nom d'utilisateur
                </label>
                <input type="text" 
                       class="form-control" 
                       id="username" 
                       name="username" 
                       required 
                       autofocus
                       placeholder="Saisissez votre nom d'utilisateur">
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-2"></i>
                    Mot de passe
                </label>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       required
                       placeholder="Saisissez votre mot de passe">
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">
                    Se souvenir de moi
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>
                Se connecter
            </button>
        </form>
        
        <hr class="my-4">
        
        <div class="text-center">
            <small class="text-muted">
                <div class="mb-2">
                    <strong>Comptes de démonstration :</strong>
                </div>
                <div class="row">
                    <div class="col-6">
                        <span class="badge bg-info">Administrateur</span><br>
                        <code>admin / admin123</code>
                    </div>
                    <div class="col-6">
                        <span class="badge bg-secondary">Utilisateur</span><br>
                        <code>user / user123</code>
                    </div>
                </div>
            </small>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                Besoin d'aide ? 
                <a href="#" class="text-decoration-none">Contactez l'administrateur</a>
            </small>
        </div>
    </div>
</div>

<!-- Login page specific styles -->
<style>
.login-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.login-card {
    width: 100%;
    max-width: 400px;
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.8em;
}
</style>

<!-- Auto-hide alerts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
});
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
