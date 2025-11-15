<?php

class Routing {
    
    public static $routes = [
        '' => [
            'view' => 'mainpage'
        ],
        
        'login' => [
            'controller' => 'SecurityController',
            'action' => 'login'
        ],
        'register' => [
            'controller' => 'SecurityController',
            'action' => 'register'
        ],
        
        'category' => [
            'controller' => 'CategoryController',
            'action' => 'index'
        ],
        
        'worker' => [
            'controller' => 'WorkerController',
            'action' => 'index'
        ]
    ];
    
    public static function run(string $url) {
        $path = parse_url($url, PHP_URL_PATH);
        $path = trim($path, '/');
        
        if(!isset(self::$routes[$path])) {
            http_response_code(404);
            $notFoundPath = __DIR__ . '/public/views/404.html';
            if(file_exists($notFoundPath)) {
                include $notFoundPath;
            } else {
                echo "404 - Page not found";
            }
            return;
        }
        
        $route = self::$routes[$path];
        
        if(isset($route['controller'])) {
            $controller = $route['controller'];
            $action = $route['action'];
            
            $controllerPath = __DIR__ . '/src/controllers/' . $controller . '.php';
            
            if(!file_exists($controllerPath)) {
                die("Controller not found: {$controllerPath}");
            }
            
            require_once $controllerPath;
            $obj = new $controller;
            $obj->$action();
            return;
        }
        
        if(isset($route['view'])) {
            $viewPath = __DIR__ . '/public/views/' . $route['view'] . '.html';
            
            if(!file_exists($viewPath)) {
                die("View not found: {$viewPath}");
            }
            
            include $viewPath;
        }
    }
}