<?php

class Routing {
    
    public static $routes = [
        '' => [
            'controller' => 'MainController',
            'action' => 'index'
        ],
        'search' => [
            'controller' => 'MainController',
            'action' => 'search'
        ],
        'login' => [
            'controller' => 'SecurityController',
            'action' => 'login'
        ],
        'register' => [
            'controller' => 'SecurityController',
            'action' => 'register'
        ],
        'logout' => [
            'controller' => 'SecurityController',
            'action' => 'logout'
        ],
        'profile' => [
            'controller' => 'ProfileController',
            'action' => 'index'
        ],
        'profile/update' => [
            'controller' => 'ProfileController',
            'action' => 'update'
        ],
        'profile/add-service' => [
            'controller' => 'ProfileController',
            'action' => 'addService'
        ],
        'profile/delete-service' => [
            'controller' => 'ProfileController',
            'action' => 'deleteService'
        ],
        'worker' => [
            'controller' => 'WorkerController',
            'action' => 'index'
        ],
        'reservations' => [
            'controller' => 'ReservationsController',
            'action' => 'index'
        ],
        'reservations/book' => [
            'controller' => 'ReservationsController',
            'action' => 'book'
        ],
        'reservations/cancel' => [
            'controller' => 'ReservationsController',
            'action' => 'cancel'
        ],
        'reservations/confirm' => [
            'controller' => 'ReservationsController',
            'action' => 'confirm'
        ],
        'reservations/complete' => [
            'controller' => 'ReservationsController',
            'action' => 'complete'
        ],
        'reservations/review' => [
            'controller' => 'ReservationsController',
            'action' => 'review'
        ]
    ];
    
    public static function run(string $url) {
        $path = parse_url($url, PHP_URL_PATH);
        $path = trim($path, '/');
        
        if (preg_match('#^category/([a-z-]+)$#', $path, $matches)) {
            require_once __DIR__ . '/src/controllers/CategoryController.php';
            $controller = new CategoryController();
            $controller->show($matches[1]);
            return;
        }
        
        if (preg_match('#^worker/(\d+)$#', $path, $matches)) {
            require_once __DIR__ . '/src/controllers/WorkerController.php';
            $controller = new WorkerController();
            $controller->show((int)$matches[1]);
            return;
        }
        
        if (!isset(self::$routes[$path])) {
            http_response_code(404);
            $notFoundPath = __DIR__ . '/public/views/404.html';
            if (file_exists($notFoundPath)) {
                include $notFoundPath;
            } else {
                echo "404 - Page not found";
            }
            return;
        }
        
        $route = self::$routes[$path];
        
        if (isset($route['controller'])) {
            $controller = $route['controller'];
            $action = $route['action'];
            
            $controllerPath = __DIR__ . '/src/controllers/' . $controller . '.php';
            
            if (!file_exists($controllerPath)) {
                die("Controller not found: {$controllerPath}");
            }
            
            require_once $controllerPath;
            $obj = new $controller;
            $obj->$action();
            return;
        }
        
        if (isset($route['view'])) {
            $viewPath = __DIR__ . '/public/views/' . $route['view'] . '.html';
            
            if (!file_exists($viewPath)) {
                die("View not found: {$viewPath}");
            }
            
            include $viewPath;
        }
    }
}
