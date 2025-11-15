<?php


class SecurityController {
    
    public function login() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // TODO: Logika logowania
            header('Location: /worker');
            exit();
        }
        
        include __DIR__ . '/../../public/views/loginpage.html';
    }
    
    public function register() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // TODO: Logika rejestracji
            header('Location: /login');
            exit();
        }
        
        include __DIR__ . '/../../public/views/registerpage.html';
    }
}
