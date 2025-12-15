<?php

class WorkerController {
    
    // Przykładowi fachowcy - w przyszłości z PostgreSQL
    private $workers = [
        1 => [
            'id' => 1,
            'name' => 'Jan Kowalski',
            'profession' => 'Hydraulik',
            'city' => 'Kraków',
            'address' => 'ul. Krakowska 15, 31-001 Kraków',
            'image' => 'person1.png',
            'rating' => 4.8,
            'reviews_count' => 127,
            'experience' => 12,
            'completed_jobs' => 543,
            'description' => 'Jestem doświadczonym hydraulikiem z ponad 12-letnim stażem. Specjalizuję się w naprawach instalacji wodnych, montażu armatury łazienkowej oraz udrażnianiu kanalizacji. Gwarantuję profesjonalną obsługę i terminowość. Pracuję na terenie całego Krakowa i okolic. Każde zlecenie traktuję indywidualnie, dbając o najwyższą jakość wykonania.',
            'services' => [
                ['name' => 'Naprawa kranów', 'price' => 80, 'duration' => '30 min'],
                ['name' => 'Udrażnianie rur', 'price' => 150, 'duration' => '1 godz'],
                ['name' => 'Montaż baterii', 'price' => 120, 'duration' => '45 min'],
                ['name' => 'Wymiana syfonu', 'price' => 60, 'duration' => '20 min'],
                ['name' => 'Montaż WC', 'price' => 250, 'duration' => '2 godz'],
                ['name' => 'Naprawa spłuczki', 'price' => 100, 'duration' => '45 min'],
            ],
            'reviews' => [
                ['author' => 'Marek W.', 'rating' => 5, 'date' => '2025-12-01', 'content' => 'Szybka i profesjonalna obsługa. Polecam!'],
                ['author' => 'Anna K.', 'rating' => 5, 'date' => '2025-11-28', 'content' => 'Pan Jan naprawił kran w 20 minut. Bardzo miły i punktualny.'],
                ['author' => 'Tomasz M.', 'rating' => 4, 'date' => '2025-11-15', 'content' => 'Dobra robota, cena zgodna z umową.'],
            ],
            'gallery' => [
                'gallery1.jpg',
                'gallery2.jpg',
                'gallery3.jpg',
                'gallery4.jpg',
                'gallery5.jpg',
                'gallery6.jpg',
            ],
            'availability' => [
                'Pon' => ['9:00', '10:00', '14:00', '15:00'],
                'Wt' => ['9:00', '11:00', '13:00', '16:00'],
                'Śr' => ['10:00', '12:00', '14:00'],
                'Czw' => ['9:00', '10:00', '11:00', '15:00'],
                'Pt' => ['9:00', '13:00', '14:00'],
            ]
        ],
        2 => [
            'id' => 2,
            'name' => 'Anna Nowak',
            'profession' => 'Elektryk',
            'city' => 'Kraków',
            'address' => 'ul. Długa 42, 31-147 Kraków',
            'image' => 'person2.png',
            'rating' => 5.0,
            'reviews_count' => 89,
            'experience' => 8,
            'completed_jobs' => 312,
            'description' => 'Wykwalifikowany elektryk z uprawnieniami SEP. Wykonuję instalacje elektryczne, naprawy oraz przeglądy. Dbam o bezpieczeństwo i jakość wykonania. Posiadam wieloletnie doświadczenie w pracy z instalacjami mieszkaniowymi i przemysłowymi.',
            'services' => [
                ['name' => 'Instalacja gniazdek', 'price' => 60, 'duration' => '30 min'],
                ['name' => 'Wymiana włączników', 'price' => 45, 'duration' => '20 min'],
                ['name' => 'Montaż lamp', 'price' => 90, 'duration' => '45 min'],
                ['name' => 'Wymiana bezpieczników', 'price' => 80, 'duration' => '30 min'],
                ['name' => 'Instalacja domofonu', 'price' => 200, 'duration' => '2 godz'],
            ],
            'reviews' => [
                ['author' => 'Piotr Z.', 'rating' => 5, 'date' => '2025-12-05', 'content' => 'Pani Anna jest bardzo kompetentna. Wszystko sprawnie i czysto.'],
                ['author' => 'Katarzyna L.', 'rating' => 5, 'date' => '2025-11-20', 'content' => 'Super robota! Polecam każdemu.'],
            ],
            'gallery' => [
                'gallery1.jpg',
                'gallery2.jpg',
                'gallery3.jpg',
                'gallery4.jpg',
            ],
            'availability' => [
                'Pon' => ['8:00', '10:00', '12:00', '14:00'],
                'Wt' => ['9:00', '11:00', '15:00'],
                'Śr' => ['8:00', '10:00', '13:00', '16:00'],
                'Czw' => ['9:00', '11:00', '14:00'],
                'Pt' => ['8:00', '10:00', '12:00'],
            ]
        ],
    ];
    
    public function index() {
        // Przekierowanie do pierwszego fachowca lub 404
        header('Location: /worker/1');
        exit;
    }
    
    public function show(int $id) {
        if (!isset($this->workers[$id])) {
            http_response_code(404);
            include __DIR__ . '/../../public/views/404.html';
            return;
        }
        
        $worker = $this->workers[$id];
        
        include __DIR__ . '/../../public/views/workerpage.php';
    }
}