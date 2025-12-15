<?php

class CategoryController {
    
    // Dane kategorii - w przyszłości będą pobierane z PostgreSQL
    private $categories = [
        'plumbing' => [
            'name' => 'Hydraulika',
            'description' => 'Profesjonalne usługi hydrauliczne',
            'icon' => 'plumbing.svg'
        ],
        'electricity' => [
            'name' => 'Elektryka',
            'description' => 'Usługi elektryczne i instalacyjne',
            'icon' => 'lightning.svg'
        ],
        'cleaning' => [
            'name' => 'Sprzątanie',
            'description' => 'Profesjonalne usługi sprzątające',
            'icon' => 'cleaning.svg'
        ],
        'painting' => [
            'name' => 'Malowanie',
            'description' => 'Usługi malarskie i dekoracyjne',
            'icon' => 'painting.svg'
        ],
        'furniture-assembly' => [
            'name' => 'Montaż mebli',
            'description' => 'Składanie i montaż mebli',
            'icon' => 'sofa.svg'
        ],
        'installation' => [
            'name' => 'Instalacja',
            'description' => 'Montaż sprzętu AGD i RTV',
            'icon' => 'installation.svg'
        ],
        'air-conditioning' => [
            'name' => 'Klimatyzacja',
            'description' => 'Montaż i serwis klimatyzacji',
            'icon' => 'air-conditioning.svg'
        ],
        'maintenance' => [
            'name' => 'Konserwacja',
            'description' => 'Przeglądy i naprawy urządzeń',
            'icon' => 'maintenance.svg'
        ]
    ];
    
    // Przykładowi fachowcy - w przyszłości z PostgreSQL
    private $workers = [
        [
            'id' => 1,
            'name' => 'Jan Kowalski',
            'address' => 'ul. Krakowska 15, 31-001 Kraków',
            'image' => 'person1.png',
            'services' => [
                ['name' => 'Naprawa kranów', 'price' => 80],
                ['name' => 'Udrażnianie rur', 'price' => 150],
                ['name' => 'Montaż baterii', 'price' => 120]
            ]
        ],
        [
            'id' => 2,
            'name' => 'Anna Nowak',
            'address' => 'ul. Długa 42, 31-147 Kraków',
            'image' => 'person2.png',
            'services' => [
                ['name' => 'Instalacja gniazdek', 'price' => 60],
                ['name' => 'Wymiana włączników', 'price' => 45],
                ['name' => 'Montaż lamp', 'price' => 90]
            ]
        ],
        [
            'id' => 3,
            'name' => 'Piotr Zieliński',
            'address' => 'ul. Marszałkowska 100, 00-001 Warszawa',
            'image' => 'person3.png',
            'services' => [
                ['name' => 'Malowanie ścian', 'price' => 25],
                ['name' => 'Malowanie sufitu', 'price' => 30],
                ['name' => 'Gładzie gipsowe', 'price' => 40]
            ]
        ],
        [
            'id' => 4,
            'name' => 'Ewa Mazur',
            'address' => 'ul. Floriańska 8, 31-021 Kraków',
            'image' => 'person4.png',
            'services' => [
                ['name' => 'Sprzątanie mieszkania', 'price' => 120],
                ['name' => 'Mycie okien', 'price' => 80],
                ['name' => 'Sprzątanie po remoncie', 'price' => 200]
            ]
        ],
        [
            'id' => 5,
            'name' => 'Tomasz Wiśniewski',
            'address' => 'ul. Gdańska 25, 80-001 Gdańsk',
            'image' => 'person1.png',
            'services' => [
                ['name' => 'Montaż szafy', 'price' => 150],
                ['name' => 'Montaż kuchni IKEA', 'price' => 400],
                ['name' => 'Składanie łóżka', 'price' => 100]
            ]
        ],
        [
            'id' => 6,
            'name' => 'Maria Kaczmarek',
            'address' => 'ul. Świdnicka 33, 50-066 Wrocław',
            'image' => 'person2.png',
            'services' => [
                ['name' => 'Serwis klimatyzacji', 'price' => 200],
                ['name' => 'Montaż klimatyzatora', 'price' => 500],
                ['name' => 'Czyszczenie klimatyzacji', 'price' => 150]
            ]
        ],
    ];
    
    public function show(string $slug) {
        if (!isset($this->categories[$slug])) {
            http_response_code(404);
            include __DIR__ . '/../../public/views/404.html';
            return;
        }
        
        $category = $this->categories[$slug];
        $categorySlug = $slug;
        $categoryName = $category['name'];
        $categoryDescription = $category['description'];
        $categoryIcon = $category['icon'];
        
        // W przyszłości: pobierz fachowców z bazy danych
        $workers = $this->workers;
        $categories = $this->categories;
        
        // Paginacja - w przyszłości z parametrów GET
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $totalWorkers = count($workers);
        $perPage = 4;
        $totalPages = ceil($totalWorkers / $perPage);
        
        include __DIR__ . '/../../public/views/categorypage.php';
    }
}

