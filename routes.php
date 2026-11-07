<?php
/**
 * HCPVision Routes Configuration
 * Defines URL routing for the application
 */

function handleRoute($uri) {
    // Remove query parameters - use more reliable method
    $questionPos = strpos($uri, '?');
    $cleanUri = $questionPos !== false ? substr($uri, 0, $questionPos) : $uri;
    
    // Handle the root route directly to avoid PHP array key issues
    if ($cleanUri === '' || $cleanUri === '/' || empty($cleanUri)) {
        // Check if user is authenticated
        if (!isAuthenticated()) {
            header('Location: /login');
            exit;
        }
        
        // Load dashboard directly
        $controllerName = 'app\\controllers\\DashboardController';
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, 'index')) {
                $controller->index();
                return;
            }
        }
        show404();
    }
    
    // Define routes for non-root URLs
    $routes = [
        'login' => ['controller' => 'AuthController', 'action' => 'login'],
        'logout' => ['controller' => 'AuthController', 'action' => 'logout'],
        'dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
        'enqueteurs' => ['controller' => 'EnqueteurController', 'action' => 'index'],
        'enqueteurs/detail' => ['controller' => 'EnqueteurController', 'action' => 'detail'],
        'regions' => ['controller' => 'RegionController', 'action' => 'index'],
        'regions/detail' => ['controller' => 'RegionController', 'action' => 'detail'],
        'anomalies' => ['controller' => 'AnomalieController', 'action' => 'index'],
        'rapports' => ['controller' => 'RapportController', 'action' => 'index'],
        'rapports/generate' => ['controller' => 'RapportController', 'action' => 'generate'],
        'comptes' => ['controller' => 'CompteController', 'action' => 'index'],
        'comptes/update' => ['controller' => 'CompteController', 'action' => 'update'],
        'parametres' => ['controller' => 'ParametreController', 'action' => 'index'],
        
        // API routes for AJAX requests
        'api/dashboard/kpi' => ['controller' => 'DashboardController', 'action' => 'apiKpi'],
        'api/dashboard/status' => ['controller' => 'DashboardController', 'action' => 'apiStatus'],
        'api/enqueteurs/performance' => ['controller' => 'EnqueteurController', 'action' => 'apiPerformance'],
        'api/regions/progress' => ['controller' => 'RegionController', 'action' => 'apiProgress'],
    ];
    
    // Check if route exists
    if (array_key_exists($cleanUri, $routes)) {
        $route = $routes[$cleanUri];
        $controllerName = 'app\\controllers\\' . $route['controller'];
        $action = $route['action'];
        
        // Check if user is authenticated (except for login page)
        if ($cleanUri !== 'login' && !isAuthenticated()) {
            header('Location: /login');
            exit;
        }
        
        // Instantiate controller and call action
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $action)) {
                $controller->$action();
            } else {
                show404();
            }
        } else {
            show404();
        }
    } else {
        show404();
    }
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has admin role
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Show 404 error page
 */
function show404() {
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Page non trouvée - HCPVision</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <h1 class="display-1">404</h1>
                    <h2>Page non trouvée</h2>
                    <p class="lead">La page que vous recherchez n\'existe pas.</p>
                    <a href="/" class="btn btn-primary">Retour à l\'accueil</a>
                </div>
            </div>
        </div>
    </body>
    </html>';
}
?>
