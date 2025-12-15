<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/styles/main.css">
    <link rel="stylesheet" href="/public/styles/worker.css">
    <link rel="stylesheet" href="/public/fontawesome/css/all.min.css">
    <title><?= htmlspecialchars($worker['name']) ?> - FixUp</title>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/" class="logo">
                <img src="/public/images/logo_fixup.svg" alt="FixUp">
            </a>
            <nav class="header-nav">
                <a href="/login" class="login-btn">Zaloguj się</a>
                <a href="/register" class="register-btn">Załóż konto</a>
            </nav>
        </div>
    </header>

    <main class="worker-page">
        <div class="worker-container">
            <aside class="worker-sidebar">
                <div class="profile-card">
                    <div class="profile-image">
                        <img src="/public/images/mainpage/<?= htmlspecialchars($worker['image']) ?>" alt="<?= htmlspecialchars($worker['name']) ?>">
                    </div>
                    <h1 class="profile-name"><?= htmlspecialchars($worker['name']) ?></h1>
                    <p class="profile-profession"><?= htmlspecialchars($worker['profession']) ?></p>
                    <p class="profile-city">
                        <i class="fa-solid fa-location-dot"></i>
                        <?= htmlspecialchars($worker['city'] ?? 'Kraków') ?>
                    </p>
                    <div class="profile-rating">
                        <i class="fa-solid fa-star"></i>
                        <span class="rating-value"><?= number_format($worker['rating'], 1) ?></span>
                        <span class="rating-count">(<?= $worker['reviews_count'] ?> opinii)</span>
                    </div>
                </div>

                <div class="booking-section">
                    <h3>Zarezerwuj termin</h3>
                    <div class="booking-calendar">
                        <div class="calendar-header">
                            <button class="calendar-nav" id="prevMonth"><i class="fa-solid fa-chevron-left"></i></button>
                            <span class="calendar-month">Grudzień 2025</span>
                            <button class="calendar-nav" id="nextMonth"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                        <div class="calendar-weekdays">
                            <span>Pon</span>
                            <span>Wt</span>
                            <span>Śr</span>
                            <span>Czw</span>
                            <span>Pt</span>
                            <span>Sob</span>
                            <span>Nie</span>
                        </div>
                        <div class="calendar-days">
                            <?php for ($i = 1; $i <= 31; $i++): ?>
                                <button class="day-btn <?= $i === 15 ? 'selected' : '' ?>"><?= $i ?></button>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="time-picker">
                        <h4>Wybierz godzinę</h4>
                        <div class="time-slots">
                            <?php 
                            $slots = ['9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
                            foreach ($slots as $index => $slot): 
                            ?>
                                <button class="time-slot <?= $index === 2 ? 'selected' : '' ?>"><?= $slot ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button class="btn-book-now">
                        <i class="fa-solid fa-calendar-check"></i>
                        Zarezerwuj
                    </button>
                </div>
            </aside>

            <div class="worker-main">
                <div class="tabs-nav">
                    <button class="tab-btn active" data-tab="about">O mnie</button>
                    <button class="tab-btn" data-tab="services">Usługi</button>
                    <button class="tab-btn" data-tab="reviews">Opinie</button>
                    <button class="tab-btn" data-tab="gallery">Galeria</button>
                </div>

                <div class="tab-content active" id="tab-about">
                    <section class="about-section">
                        <h2>O mnie</h2>
                        <p><?= htmlspecialchars($worker['description']) ?></p>
                    </section>

                    <section class="services-preview">
                        <div class="section-header">
                            <h2>Popularne usługi</h2>
                            <a href="#" class="see-more" data-tab-link="services">Zobacz wszystkie <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                        <div class="services-list">
                            <?php 
                            $topServices = array_slice($worker['services'], 0, 3);
                            foreach ($topServices as $service): 
                            ?>
                            <div class="service-item">
                                <div class="service-info">
                                    <span class="service-name"><?= htmlspecialchars($service['name']) ?></span>
                                    <span class="service-duration">
                                        <i class="fa-regular fa-clock"></i>
                                        <?= htmlspecialchars($service['duration']) ?>
                                    </span>
                                </div>
                                <div class="service-action">
                                    <span class="service-price"><?= $service['price'] ?> zł</span>
                                    <button class="btn-service-book">Umów</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="reviews-preview">
                        <div class="section-header">
                            <h2>Opinie</h2>
                            <a href="#" class="see-more" data-tab-link="reviews">Zobacz wszystkie <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                        <div class="reviews-summary">
                            <div class="rating-big">
                                <i class="fa-solid fa-star"></i>
                                <span><?= number_format($worker['rating'], 1) ?></span>
                            </div>
                            <span class="reviews-count"><?= $worker['reviews_count'] ?> opinii</span>
                        </div>
                        <div class="reviews-list">
                            <?php 
                            $topReviews = array_slice($worker['reviews'], 0, 2);
                            foreach ($topReviews as $review): 
                            ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="review-author">
                                        <div class="author-avatar">
                                            <?= strtoupper(substr($review['author'], 0, 1)) ?>
                                        </div>
                                        <div class="author-info">
                                            <span class="author-name"><?= htmlspecialchars($review['author']) ?></span>
                                            <span class="review-date"><?= date('d.m.Y', strtotime($review['date'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                            <i class="fa-solid fa-star"></i>
                                        <?php endfor; ?>
                                        <?php for ($i = $review['rating']; $i < 5; $i++): ?>
                                            <i class="fa-regular fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="review-content"><?= htmlspecialchars($review['content']) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="gallery-preview">
                        <div class="section-header">
                            <h2>Galeria</h2>
                            <a href="#" class="see-more" data-tab-link="gallery">Zobacz wszystkie <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                        <div class="gallery-grid">
                            <?php 
                            $previewGallery = array_slice($worker['gallery'] ?? [], 0, 4);
                            foreach ($previewGallery as $image): 
                            ?>
                            <div class="gallery-item">
                                <img src="/public/images/gallery/<?= htmlspecialchars($image) ?>" alt="Realizacja">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <div class="tab-content" id="tab-services">
                    <section class="services-full">
                        <h2>Wszystkie usługi</h2>
                        <div class="services-list">
                            <?php foreach ($worker['services'] as $service): ?>
                            <div class="service-item">
                                <div class="service-info">
                                    <span class="service-name"><?= htmlspecialchars($service['name']) ?></span>
                                    <span class="service-duration">
                                        <i class="fa-regular fa-clock"></i>
                                        <?= htmlspecialchars($service['duration']) ?>
                                    </span>
                                </div>
                                <div class="service-action">
                                    <span class="service-price"><?= $service['price'] ?> zł</span>
                                    <button class="btn-service-book">Umów</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <div class="tab-content" id="tab-reviews">
                    <section class="reviews-full">
                        <div class="reviews-header">
                            <h2>Wszystkie opinie</h2>
                            <div class="reviews-summary">
                                <div class="rating-big">
                                    <i class="fa-solid fa-star"></i>
                                    <span><?= number_format($worker['rating'], 1) ?></span>
                                </div>
                                <span class="reviews-count"><?= $worker['reviews_count'] ?> opinii</span>
                            </div>
                        </div>
                        <div class="reviews-list">
                            <?php foreach ($worker['reviews'] as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="review-author">
                                        <div class="author-avatar">
                                            <?= strtoupper(substr($review['author'], 0, 1)) ?>
                                        </div>
                                        <div class="author-info">
                                            <span class="author-name"><?= htmlspecialchars($review['author']) ?></span>
                                            <span class="review-date"><?= date('d.m.Y', strtotime($review['date'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                            <i class="fa-solid fa-star"></i>
                                        <?php endfor; ?>
                                        <?php for ($i = $review['rating']; $i < 5; $i++): ?>
                                            <i class="fa-regular fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="review-content"><?= htmlspecialchars($review['content']) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <div class="tab-content" id="tab-gallery">
                    <section class="gallery-full">
                        <h2>Galeria realizacji</h2>
                        <div class="gallery-grid full">
                            <?php foreach ($worker['gallery'] ?? [] as $image): ?>
                            <div class="gallery-item">
                                <img src="/public/images/gallery/<?= htmlspecialchars($image) ?>" alt="Realizacja">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <h4>O NAS</h4>
                <a href="#">O firmie</a>
                <a href="#">Kariera</a>
                <a href="#">Prasa</a>
            </div>
            <div class="footer-column">
                <h4>WSPARCIE</h4>
                <a href="#">FAQ</a>
                <a href="#">Kontakt</a>
                <a href="#">Centrum pomocy</a>
            </div>
            <div class="footer-column">
                <h4>PRAWNE</h4>
                <a href="#">Regulamin</a>
                <a href="#">Polityka prywatności</a>
            </div>
            <div class="footer-logo">
                <img src="/public/images/logo_fixup.svg" alt="FixUp">
            </div>
        </div>
        <div class="footer-bottom">
            © 2025 FixUp. Wszelkie prawa zastrzeżone.
        </div>
    </footer>
    <script src="public/scripts/worker.js"></script>                            
</body>
</html>
