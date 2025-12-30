<?php

require_once __DIR__ . '/../repository/WorkerRepository.php';
require_once __DIR__ . '/../repository/CategoryRepository.php';
require_once __DIR__ . '/SecurityController.php';

class MainController {

    public function index() {
        $workerRepo = new WorkerRepository();
        $categoryRepo = new CategoryRepository();

        $featuredWorkers = $workerRepo->getFeatured(4);
        $categories = $categoryRepo->findAll();
        $user = SecurityController::getCurrentUser();

        include __DIR__ . '/../../public/views/mainpage.php';
    }

    public function search() {
        $query = trim($_GET['q'] ?? '');
        $city = trim($_GET['city'] ?? '');

        $workerRepo = new WorkerRepository();
        $workers = $workerRepo->search($query, $city ?: null);
        $user = SecurityController::getCurrentUser();

        $searchQuery = $query;
        $searchCity = $city;

        include __DIR__ . '/../../public/views/searchresults.php';
    }
}

