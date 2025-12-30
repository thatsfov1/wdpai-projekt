<?php

require_once __DIR__ . '/../repository/CategoryRepository.php';
require_once __DIR__ . '/../repository/WorkerRepository.php';
require_once __DIR__ . '/../repository/ServiceRepository.php';
require_once __DIR__ . '/SecurityController.php';

class CategoryController {
    
    public function show(string $slug) {
        $categoryRepo = new CategoryRepository();
        $workerRepo = new WorkerRepository();
        $serviceRepo = new ServiceRepository();
        
        $category = $categoryRepo->findBySlug($slug);
        
        if (!$category) {
            http_response_code(404);
            include __DIR__ . '/../../public/views/404.html';
            return;
        }
        
        $city = trim($_GET['city'] ?? '');
        $sort = trim($_GET['sort'] ?? 'rating');
        $minRating = isset($_GET['min_rating']) ? floatval($_GET['min_rating']) : null;
        $minExperience = isset($_GET['min_experience']) ? intval($_GET['min_experience']) : null;
        $priceFrom = isset($_GET['price_from']) ? floatval($_GET['price_from']) : null;
        $priceTo = isset($_GET['price_to']) ? floatval($_GET['price_to']) : null;
        
        $allWorkers = $workerRepo->findByCategorySlugWithFilters(
            $slug, 
            $city ?: null, 
            $sort ?: null,
            $minRating,
            $minExperience,
            $priceFrom,
            $priceTo
        );
        
        $categoriesRaw = $categoryRepo->findAll();
        $categories = [];
        foreach ($categoriesRaw as $cat) {
            $categories[$cat['slug']] = $cat;
        }
        
        $user = SecurityController::getCurrentUser();
        
        $categoryName = $category['name'];
        $categorySlug = $category['slug'];
        $categoryDescription = 'Znajdź najlepszych fachowców w kategorii ' . $category['name'];
        
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 4;
        $totalWorkers = count($allWorkers);
        $totalPages = max(1, ceil($totalWorkers / $perPage));
        $currentPage = min($currentPage, $totalPages);
        
        $offset = ($currentPage - 1) * $perPage;
        $workersSlice = array_slice($allWorkers, $offset, $perPage);
        
        $workers = [];
        foreach ($workersSlice as $w) {
            $services = $serviceRepo->findByWorkerId($w['id']);
            $formattedServices = [];
            foreach ($services as $s) {
                $formattedServices[] = [
                    'name' => $s['name'],
                    'price' => $s['price'] ?? 0
                ];
            }
            
            if (empty($formattedServices)) {
                $formattedServices[] = [
                    'name' => 'Usługa standardowa',
                    'price' => 0
                ];
            }
            
            $workers[] = [
                'id' => $w['id'],
                'name' => $w['name'],
                'image' => $w['profile_image'] 
                    ? '/uploads/profiles/' . $w['profile_image'] 
                    : '/public/images/default-avatar.svg',
                'address' => $w['city'] ?? 'Brak adresu',
                'rating' => $w['rating'] ?? 0,
                'reviews_count' => $w['reviews_count'] ?? 0,
                'services' => $formattedServices
            ];
        }
        
        include __DIR__ . '/../../public/views/categorypage.php';
    }
}
