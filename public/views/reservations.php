<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/styles/main.css">
    <link rel="stylesheet" href="/public/styles/mainpage.css">
    <link rel="stylesheet" href="/public/styles/reservations.css">
    <link rel="stylesheet" href="/public/fontawesome/css/all.min.css">
    <title>Moje rezerwacje - FixUp</title>
</head>
<body>
    <header class="nav">
        <a href="/">
            <img src="/public/images/logo_fixup.svg" alt="FixUp" class="logo" />
        </a>

        <nav class="nav-links">
            <a href="/#categories">Usługi</a>
            <a href="/#steps">Jak to działa</a>
        </nav>

        <div class="nav-actions">
            <?php if ($user): ?>
                <div class="profile-dropdown">
                    <button class="profile-trigger">
                        <?php 
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
                        <a href="/reservations" class="active"><i class="fa-solid fa-calendar-check"></i> Moje rezerwacje</a>
                        <?php if ($user['role'] === 'worker'): ?>
                            <a href="/profile#services"><i class="fa-solid fa-briefcase"></i> Moje usługi</a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="/logout" class="logout-link"><i class="fa-solid fa-sign-out-alt"></i> Wyloguj się</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="reservations-page container">
        <div class="page-header">
            <h1><i class="fa-solid fa-calendar-check"></i> Moje rezerwacje</h1>
            <p><?= $isWorkerView ? 'Zarządzaj rezerwacjami od klientów' : 'Przeglądaj swoje rezerwacje u fachowców' ?></p>
        </div>

        <?php if (isset($_SESSION['reservation_success'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['reservation_success']) ?>
            </div>
            <?php unset($_SESSION['reservation_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['reservation_error'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['reservation_error']) ?>
            </div>
            <?php unset($_SESSION['reservation_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['booking_success'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['booking_success']) ?>
            </div>
            <?php unset($_SESSION['booking_success']); ?>
        <?php endif; ?>

        <div class="tabs">
            <a href="/reservations?tab=current" class="tab <?= $tab === 'current' ? 'active' : '' ?>">
                <i class="fa-solid fa-clock"></i>
                Aktualne
                <?php if (count($currentReservations) > 0): ?>
                    <span class="tab-badge"><?= count($currentReservations) ?></span>
                <?php endif; ?>
            </a>
            <a href="/reservations?tab=history" class="tab <?= $tab === 'history' ? 'active' : '' ?>">
                <i class="fa-solid fa-history"></i>
                Historia
            </a>
        </div>

        <div class="reservations-list">
            <?php 
            $reservations = $tab === 'current' ? $currentReservations : $historyReservations;
            
            if (empty($reservations)): 
            ?>
                <div class="empty-state">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <h3><?= $tab === 'current' ? 'Brak aktualnych rezerwacji' : 'Brak rezerwacji w historii' ?></h3>
                    <p><?= $isWorkerView 
                        ? 'Gdy klienci zarezerwują Twoje usługi, zobaczysz je tutaj.' 
                        : 'Znajdź fachowca i zarezerwuj termin!' ?></p>
                    <?php if (!$isWorkerView): ?>
                        <a href="/#categories" class="btn btn-primary">Znajdź fachowca</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($reservations as $reservation): ?>
                    <div class="reservation-card <?= $reservation['status'] ?>">
                        <div class="reservation-image">
                            <?php 
                            $image = $isWorkerView 
                                ? ($reservation['client_image'] ?? null)
                                : ($reservation['worker_image'] ?? null);
                            $imageUrl = $image ? '/uploads/profiles/' . $image : '/public/images/default-avatar.svg';
                            ?>
                            <img src="<?= htmlspecialchars($imageUrl) ?>" alt="Zdjęcie">
                        </div>
                        
                        <div class="reservation-details">
                            <div class="reservation-header">
                                <h3>
                                    <?php if ($isWorkerView): ?>
                                        <?= htmlspecialchars($reservation['client_name']) ?>
                                    <?php else: ?>
                                        <a href="/worker/<?= $reservation['worker_id'] ?>">
                                            <?= htmlspecialchars($reservation['worker_name']) ?>
                                        </a>
                                    <?php endif; ?>
                                </h3>
                                <span class="status-badge status-<?= $reservation['status'] ?>">
                                    <?php
                                    $statusLabels = [
                                        'pending' => 'Oczekuje na potwierdzenie',
                                        'confirmed' => 'Potwierdzona',
                                        'completed' => 'Zakończona',
                                        'cancelled' => 'Anulowana'
                                    ];
                                    echo $statusLabels[$reservation['status']] ?? $reservation['status'];
                                    ?>
                                </span>
                            </div>
                            
                            <?php if (!$isWorkerView && isset($reservation['category_name'])): ?>
                                <p class="reservation-category">
                                    <i class="fa-solid fa-tag"></i>
                                    <?= htmlspecialchars($reservation['category_name']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($reservation['service_name']): ?>
                                <p class="reservation-service">
                                    <i class="fa-solid fa-wrench"></i>
                                    <?= htmlspecialchars($reservation['service_name']) ?>
                                    <?php if ($reservation['service_price']): ?>
                                        - <strong><?= number_format($reservation['service_price'], 2) ?> zł</strong>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="reservation-datetime">
                                <span class="date">
                                    <i class="fa-solid fa-calendar"></i>
                                    <?= date('d.m.Y', strtotime($reservation['reservation_date'])) ?>
                                </span>
                                <span class="time">
                                    <i class="fa-solid fa-clock"></i>
                                    <?= date('H:i', strtotime($reservation['reservation_time'])) ?>
                                </span>
                            </div>
                            
                            <?php if ($reservation['notes']): ?>
                                <p class="reservation-notes">
                                    <i class="fa-solid fa-sticky-note"></i>
                                    <?= htmlspecialchars($reservation['notes']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($isWorkerView && isset($reservation['client_phone']) && $reservation['client_phone']): ?>
                                <p class="reservation-phone">
                                    <i class="fa-solid fa-phone"></i>
                                    <a href="tel:<?= htmlspecialchars($reservation['client_phone']) ?>">
                                        <?= htmlspecialchars($reservation['client_phone']) ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="reservation-actions">
                            <?php if ($reservation['status'] === 'pending'): ?>
                                <?php if ($isWorkerView): ?>
                                    <a href="/reservations/confirm?id=<?= $reservation['id'] ?>" class="btn btn-success">
                                        <i class="fa-solid fa-check"></i> Potwierdź
                                    </a>
                                <?php endif; ?>
                                <a href="/reservations/cancel?id=<?= $reservation['id'] ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Czy na pewno chcesz anulować tę rezerwację?')">
                                    <i class="fa-solid fa-times"></i> Anuluj
                                </a>
                            <?php elseif ($reservation['status'] === 'confirmed' && $isWorkerView): ?>
                                <a href="/reservations/complete?id=<?= $reservation['id'] ?>" class="btn btn-primary">
                                    <i class="fa-solid fa-check-double"></i> Oznacz jako wykonane
                                </a>
                                <a href="/reservations/cancel?id=<?= $reservation['id'] ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Czy na pewno chcesz anulować tę rezerwację?')">
                                    <i class="fa-solid fa-times"></i> Anuluj
                                </a>
                            <?php elseif ($reservation['status'] === 'completed' && !$isWorkerView && isset($reservation['can_review']) && $reservation['can_review']): ?>
                                <button class="btn btn-primary" onclick="openReviewModal(<?= $reservation['id'] ?>, <?= $reservation['worker_id'] ?>, '<?= htmlspecialchars($reservation['worker_name'], ENT_QUOTES) ?>')">
                                    <i class="fa-solid fa-star"></i> Oceń
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Review Modal -->
    <div class="modal-overlay" id="reviewModal">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fa-solid fa-star"></i> Oceń fachowca</h2>
                <button class="modal-close" onclick="closeReviewModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="/reservations/review" method="POST" enctype="multipart/form-data" class="modal-body">
                <input type="hidden" name="reservation_id" id="reviewReservationId">
                <input type="hidden" name="worker_id" id="reviewWorkerId">
                
                <p class="review-worker-name" id="reviewWorkerName"></p>
                
                <div class="rating-input">
                    <label>Twoja ocena</label>
                    <div class="star-rating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" required>
                            <label for="star<?= $i ?>"><i class="fa-solid fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reviewComment">Twoja opinia</label>
                    <textarea name="comment" id="reviewComment" rows="4" placeholder="Opisz swoje doświadczenie z tym fachowcem..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="reviewImages">Dodaj zdjęcia (opcjonalnie)</label>
                    <div class="image-upload">
                        <input type="file" name="images[]" id="reviewImages" multiple accept="image/*">
                        <div class="upload-placeholder">
                            <i class="fa-solid fa-cloud-upload-alt"></i>
                            <span>Kliknij lub przeciągnij zdjęcia</span>
                        </div>
                        <div class="image-preview" id="imagePreview"></div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Anuluj</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane"></i> Wyślij opinię
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-bottom">© 2025 FixUp. Wszelkie prawa zastrzeżone.</div>
    </footer>

    <script src="/public/scripts/mainpage.js"></script>
    <script src="/public/scripts/reservations.js"></script>
</body>
</html>

