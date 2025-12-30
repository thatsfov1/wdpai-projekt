<?php

require_once __DIR__ . '/../repository/WorkerRepository.php';
require_once __DIR__ . '/../repository/ServiceRepository.php';
require_once __DIR__ . '/../repository/ReviewRepository.php';
require_once __DIR__ . '/SecurityController.php';

class WorkerController {
    
    public function index() {
        header('Location: /');
        exit();
    }
    
    public function show(int $id) {
        $workerRepo = new WorkerRepository();
        $serviceRepo = new ServiceRepository();
        $reviewRepo = new ReviewRepository();
        
        $worker = $workerRepo->findById($id);
        
        if (!$worker) {
            http_response_code(404);
            include __DIR__ . '/../../public/views/404.html';
            return;
        }
        
        $services = $serviceRepo->findByWorkerId($id);
        
        $reviews = $reviewRepo->findByWorkerId($id);
        
        $workImages = [];
        
        $user = SecurityController::getCurrentUser();
        
        include __DIR__ . '/../../public/views/workerpage.php';
    }
}
