<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/styles/main.css">
    <link rel="stylesheet" href="/public/styles/mainpage.css">
    <link rel="stylesheet" href="/public/styles/workerpage.css">
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
                <?php if ($user): ?>
                    <div class="profile-dropdown">
                        <button class="profile-trigger">
                            <?php
                                require_once __DIR__ . '/../../src/repository/UserRepository.php';
                                $userRepo = new UserRepository();
                                $fullUser = $userRepo->findById($user['id']);
                                $profileImage = $fullUser ? $fullUser->getProfileImageUrl() : '/public/images/default-avatar.svg';
                            ?>
                            <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profil" class="profile-avatar" />
                            <span><?= htmlspecialchars($user['name']) ?></span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="/profile"><i class="fa-solid fa-user"></i> Mój profil</a>
                            <a href="/reservations"><i class="fa-solid fa-calendar-check"></i> Moje rezerwacje</a>
                            <?php if ($user['role'] === 'worker'): ?>
                                <a href="/profile#services"><i class="fa-solid fa-briefcase"></i> Moje usługi</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="/logout" class="logout-link"><i class="fa-solid fa-sign-out-alt"></i> Wyloguj się</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/login" class="login-btn">Zaloguj się</a>
                    <a href="/register" class="register-btn">Załóż konto</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="worker-page">
        <div class="worker-container">
            <aside class="worker-sidebar">
                <div class="profile-card">
                    <div class="profile-image">
                        <?php 
                        $workerImage = $worker['profile_image'] 
                            ? '/uploads/profiles/' . $worker['profile_image'] 
                            : '/public/images/default-avatar.svg';
                        ?>
                        <img src="<?= htmlspecialchars($workerImage) ?>" alt="<?= htmlspecialchars($worker['name']) ?>">
                    </div>
                    <h1 class="profile-name"><?= htmlspecialchars($worker['name']) ?></h1>
                    <p class="profile-profession"><?= htmlspecialchars($worker['category_name']) ?></p>
                    <p class="profile-city">
                        <i class="fa-solid fa-location-dot"></i>
                        <?= htmlspecialchars($worker['city'] ?? 'Nie podano') ?>
                    </p>
                    <div class="profile-rating">
                        <?php 
                        $rating = floatval($worker['rating']);
                        for ($i = 1; $i <= 5; $i++): 
                            if ($i <= $rating): ?>
                                <i class="fa-solid fa-star"></i>
                            <?php elseif ($i - 0.5 <= $rating): ?>
                                <i class="fa-solid fa-star-half-stroke"></i>
                            <?php else: ?>
                                <i class="fa-regular fa-star"></i>
                            <?php endif;
                        endfor; 
                        ?>
                        <span class="rating-value"><?= number_format($rating, 1) ?></span>
                        <span class="rating-count">(<?= $worker['reviews_count'] ?> opinii)</span>
                    </div>
                    <?php if ($worker['hourly_rate']): ?>
                        <p class="profile-rate">
                            <i class="fa-solid fa-coins"></i>
                            <?= number_format($worker['hourly_rate'], 2) ?> zł/godz.
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['booking_error'])): ?>
                    <div class="alert alert-error">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <?= htmlspecialchars($_SESSION['booking_error']) ?>
                    </div>
                    <?php unset($_SESSION['booking_error']); ?>
                <?php endif; ?>

                <div class="booking-section">
                    <h3><i class="fa-solid fa-calendar-plus"></i> Zarezerwuj termin</h3>
                    
                    <?php if ($user): ?>
                        <form action="/reservations/book" method="POST" id="bookingForm">
                            <input type="hidden" name="worker_id" value="<?= $worker['id'] ?>">
                            
                            <?php if (!empty($services)): ?>
                                <div class="form-group">
                                    <label for="service_id">Wybierz usługę</label>
                                    <select name="service_id" id="service_id">
                                        <option value="">-- Dowolna usługa --</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?= $service['id'] ?>" data-price="<?= $service['price'] ?>">
                                                <?= htmlspecialchars($service['name']) ?>
                                                <?php if ($service['price']): ?>
                                                    - <?= number_format($service['price'], 2) ?> zł
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="booking_date">Data</label>
                                <input type="date" name="date" id="booking_date" 
                                       min="<?= date('Y-m-d') ?>" 
                                       value="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label>Godzina</label>
                                <div class="time-slots">
                                    <?php 
                                    $slots = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
                                    foreach ($slots as $index => $slot): 
                                    ?>
                                        <label class="time-slot-label">
                                            <input type="radio" name="time" value="<?= $slot ?>" <?= $index === 0 ? 'checked' : '' ?> required>
                                            <span class="time-slot"><?= $slot ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="booking_notes">Notatki (opcjonalnie)</label>
                                <textarea name="notes" id="booking_notes" rows="3" placeholder="Opisz czego potrzebujesz..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn-book-now">
                                <i class="fa-solid fa-calendar-check"></i>
                                Zarezerwuj termin
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="login-prompt">
                            <p>Zaloguj się, aby zarezerwować termin</p>
                            <a href="/login" class="btn btn-primary">Zaloguj się</a>
                        </div>
                    <?php endif; ?>
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
                        <p><?= nl2br(htmlspecialchars($worker['description'] ?? '')) ?: '<em>Fachowiec nie dodał jeszcze opisu.</em>' ?></p>
                        
                        <div class="worker-stats">
                            <div class="stat">
                                <i class="fa-solid fa-briefcase"></i>
                                <span class="stat-value"><?= $worker['experience_years'] ?></span>
                                <span class="stat-label">lat doświadczenia</span>
                            </div>
                            <div class="stat">
                                <i class="fa-solid fa-star"></i>
                                <span class="stat-value"><?= number_format($worker['rating'], 1) ?></span>
                                <span class="stat-label">ocena</span>
                            </div>
                            <div class="stat">
                                <i class="fa-solid fa-comments"></i>
                                <span class="stat-value"><?= $worker['reviews_count'] ?></span>
                                <span class="stat-label">opinii</span>
                            </div>
                        </div>
                    </section>

                    <?php if (!empty($services)): ?>
                    <section class="services-preview">
                        <div class="section-header">
                            <h2>Popularne usługi</h2>
                            <a href="#" class="see-more" data-tab-link="services">Zobacz wszystkie <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                        <div class="services-list">
                            <?php 
                            $topServices = array_slice($services, 0, 3);
                            foreach ($topServices as $service): 
                            ?>
                            <div class="service-item">
                                <div class="service-info">
                                    <span class="service-name"><?= htmlspecialchars($service['name']) ?></span>
                                    <?php if ($service['description']): ?>
                                        <span class="service-desc"><?= htmlspecialchars($service['description']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="service-action">
                                    <?php if ($service['price']): ?>
                                        <span class="service-price"><?= number_format($service['price'], 2) ?> zł</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php if (!empty($reviews)): ?>
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
                            $topReviews = array_slice($reviews, 0, 2);
                            foreach ($topReviews as $review): 
                            ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="review-author">
                                        <div class="author-avatar">
                                            <?php if ($review['client_image']): ?>
                                                <img src="/uploads/profiles/<?= htmlspecialchars($review['client_image']) ?>" alt="">
                                            <?php else: ?>
                                                <?= strtoupper(substr($review['client_name'], 0, 1)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="author-info">
                                            <span class="author-name"><?= htmlspecialchars($review['client_name']) ?></span>
                                            <span class="review-date"><?= date('d.m.Y', strtotime($review['created_at'])) ?></span>
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
                                <p class="review-content"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                <?php if (!empty($review['images'])): ?>
                                    <div class="review-images">
                                        <?php foreach ($review['images'] as $image): ?>
                                            <img src="/uploads/reviews/<?= htmlspecialchars($image['image_path']) ?>" alt="Zdjęcie" class="review-image">
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>

                <div class="tab-content" id="tab-services">
                    <section class="services-full">
                        <h2>Wszystkie usługi</h2>
                        <?php if (empty($services)): ?>
                            <p class="no-data">Ten fachowiec nie dodał jeszcze żadnych usług.</p>
                        <?php else: ?>
                            <div class="services-list">
                                <?php foreach ($services as $service): ?>
                                <div class="service-item">
                                    <div class="service-info">
                                        <span class="service-name"><?= htmlspecialchars($service['name']) ?></span>
                                        <?php if ($service['description']): ?>
                                            <span class="service-desc"><?= htmlspecialchars($service['description']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="service-action">
                                        <?php if ($service['price']): ?>
                                            <span class="service-price"><?= number_format($service['price'], 2) ?> zł</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
                        <?php if (empty($reviews)): ?>
                            <p class="no-data">Ten fachowiec nie ma jeszcze żadnych opinii.</p>
                        <?php else: ?>
                            <div class="reviews-list">
                                <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="review-author">
                                            <div class="author-avatar">
                                                <?php if ($review['client_image']): ?>
                                                    <img src="/uploads/profiles/<?= htmlspecialchars($review['client_image']) ?>" alt="">
                                                <?php else: ?>
                                                    <?= strtoupper(substr($review['client_name'], 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="author-info">
                                                <span class="author-name"><?= htmlspecialchars($review['client_name']) ?></span>
                                                <span class="review-date"><?= date('d.m.Y', strtotime($review['created_at'])) ?></span>
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
                                    <p class="review-content"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                    <?php if (!empty($review['images'])): ?>
                                        <div class="review-images">
                                            <?php foreach ($review['images'] as $image): ?>
                                                <img src="/uploads/reviews/<?= htmlspecialchars($image['image_path']) ?>" alt="Zdjęcie" class="review-image">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>

                <div class="tab-content" id="tab-gallery">
                    <section class="gallery-full">
                        <h2>Galeria realizacji</h2>
                        <?php if (empty($workImages)): ?>
                            <p class="no-data">Ten fachowiec nie dodał jeszcze żadnych zdjęć.</p>
                        <?php else: ?>
                            <div class="gallery-grid full">
                                <?php foreach ($workImages as $image): ?>
                                <div class="gallery-item">
                                    <img src="/uploads/work/<?= htmlspecialchars($image['image_path']) ?>" alt="<?= htmlspecialchars($image['description'] ?? 'Realizacja') ?>">
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
    <script src="/public/scripts/worker.js"></script>
    <script src="/public/scripts/mainpage.js"></script>
</body>
</html>
