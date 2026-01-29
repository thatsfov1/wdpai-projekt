CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('client', 'worker')),
    phone VARCHAR(20),
    city VARCHAR(100),
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS workers (
    id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    category_id INTEGER REFERENCES categories(id),
    description TEXT,
    experience_years INTEGER DEFAULT 0,
    hourly_rate DECIMAL(10,2),
    rating DECIMAL(2,1) DEFAULT 0,
    reviews_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS services (
    id SERIAL PRIMARY KEY,
    worker_id INTEGER REFERENCES workers(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS work_images (
    id SERIAL PRIMARY KEY,
    worker_id INTEGER REFERENCES workers(id) ON DELETE CASCADE,
    image_path VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reservations (
    id SERIAL PRIMARY KEY,
    worker_id INTEGER REFERENCES workers(id) ON DELETE CASCADE,
    client_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    service_id INTEGER REFERENCES services(id) ON DELETE SET NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'completed', 'cancelled')),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reviews (
    id SERIAL PRIMARY KEY,
    worker_id INTEGER REFERENCES workers(id) ON DELETE CASCADE,
    client_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    reservation_id INTEGER REFERENCES reservations(id) ON DELETE SET NULL,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS review_images (
    id SERIAL PRIMARY KEY,
    review_id INTEGER REFERENCES reviews(id) ON DELETE CASCADE,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS login_attempts (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE
);

CREATE INDEX IF NOT EXISTS idx_login_attempts_email ON login_attempts(email);
CREATE INDEX IF NOT EXISTS idx_login_attempts_ip ON login_attempts(ip_address);
CREATE INDEX IF NOT EXISTS idx_login_attempts_time ON login_attempts(attempted_at);
CREATE INDEX IF NOT EXISTS idx_workers_category ON workers(category_id);
CREATE INDEX IF NOT EXISTS idx_workers_rating ON workers(rating DESC);
CREATE INDEX IF NOT EXISTS idx_reservations_worker ON reservations(worker_id);
CREATE INDEX IF NOT EXISTS idx_reservations_client ON reservations(client_id);
CREATE INDEX IF NOT EXISTS idx_reservations_date ON reservations(reservation_date);
CREATE INDEX IF NOT EXISTS idx_reservations_status ON reservations(status);
CREATE INDEX IF NOT EXISTS idx_reviews_worker ON reviews(worker_id);
CREATE INDEX IF NOT EXISTS idx_users_city ON users(city);

-- widoki

-- pelne informacje o fachowcach
CREATE OR REPLACE VIEW v_workers_full AS
SELECT 
    w.id AS worker_id,
    u.id AS user_id,
    u.name,
    u.email,
    u.phone,
    u.city,
    u.profile_image,
    c.id AS category_id,
    c.name AS category_name,
    c.slug AS category_slug,
    w.description,
    w.experience_years,
    w.hourly_rate,
    w.rating,
    w.reviews_count,
    w.created_at
FROM workers w
JOIN users u ON w.user_id = u.id
JOIN categories c ON w.category_id = c.id;

-- szczegolowe informacje o rezerwacjach
CREATE OR REPLACE VIEW v_reservations_details AS
SELECT 
    r.id AS reservation_id,
    r.reservation_date,
    r.reservation_time,
    r.status,
    r.notes,
    r.created_at,
    r.updated_at,
    w.id AS worker_id,
    wu.name AS worker_name,
    wu.phone AS worker_phone,
    wu.email AS worker_email,
    wu.city AS worker_city,
    wu.profile_image AS worker_image,
    cu.id AS client_id,
    cu.name AS client_name,
    cu.phone AS client_phone,
    cu.email AS client_email,
    s.id AS service_id,
    s.name AS service_name,
    s.price AS service_price,
    c.name AS category_name
FROM reservations r
JOIN workers w ON r.worker_id = w.id
JOIN users wu ON w.user_id = wu.id
JOIN users cu ON r.client_id = cu.id
LEFT JOIN services s ON r.service_id = s.id
JOIN categories c ON w.category_id = c.id;

-- statystyki fachowcow
CREATE OR REPLACE VIEW v_workers_statistics AS
SELECT 
    w.id AS worker_id,
    u.name AS worker_name,
    c.name AS category_name,
    w.rating,
    w.reviews_count,
    COUNT(DISTINCT r.id) AS total_reservations,
    COUNT(DISTINCT CASE WHEN r.status = 'completed' THEN r.id END) AS completed_reservations,
    COUNT(DISTINCT CASE WHEN r.status = 'cancelled' THEN r.id END) AS cancelled_reservations,
    COUNT(DISTINCT CASE WHEN r.status = 'pending' THEN r.id END) AS pending_reservations,
    COUNT(DISTINCT s.id) AS services_count,
    COALESCE(AVG(s.price), 0) AS avg_service_price,
    COALESCE(SUM(CASE WHEN r.status = 'completed' THEN s.price ELSE 0 END), 0) AS total_earnings
FROM workers w
JOIN users u ON w.user_id = u.id
JOIN categories c ON w.category_id = c.id
LEFT JOIN reservations r ON w.id = r.worker_id
LEFT JOIN services s ON w.id = s.worker_id
GROUP BY w.id, u.name, c.name, w.rating, w.reviews_count;

-- ranking fachowcow
CREATE OR REPLACE VIEW v_top_workers AS
SELECT 
    w.id AS worker_id,
    u.name,
    u.city,
    c.name AS category_name,
    c.slug AS category_slug,
    w.rating,
    w.reviews_count,
    w.experience_years,
    w.hourly_rate,
    RANK() OVER (ORDER BY w.rating DESC, w.reviews_count DESC) AS overall_rank,
    RANK() OVER (PARTITION BY c.id ORDER BY w.rating DESC, w.reviews_count DESC) AS category_rank
FROM workers w
JOIN users u ON w.user_id = u.id
JOIN categories c ON w.category_id = c.id
WHERE w.reviews_count >= 1;

-- ostatnie recenzje
CREATE OR REPLACE VIEW v_recent_reviews AS
SELECT 
    rv.id AS review_id,
    rv.rating,
    rv.comment,
    rv.created_at,
    cu.name AS reviewer_name,
    cu.profile_image AS reviewer_image,
    w.id AS worker_id,
    wu.name AS worker_name,
    c.name AS category_name,
    r.reservation_date,
    s.name AS service_name
FROM reviews rv
JOIN users cu ON rv.client_id = cu.id
JOIN workers w ON rv.worker_id = w.id
JOIN users wu ON w.user_id = wu.id
JOIN categories c ON w.category_id = c.id
LEFT JOIN reservations r ON rv.reservation_id = r.id
LEFT JOIN services s ON r.service_id = s.id
ORDER BY rv.created_at DESC;

-- statystyki kategorii
CREATE OR REPLACE VIEW v_category_statistics AS
SELECT 
    c.id AS category_id,
    c.name AS category_name,
    c.slug,
    COUNT(DISTINCT w.id) AS workers_count,
    COALESCE(AVG(w.rating), 0) AS avg_rating,
    COALESCE(AVG(w.hourly_rate), 0) AS avg_hourly_rate,
    COALESCE(MIN(w.hourly_rate), 0) AS min_hourly_rate,
    COALESCE(MAX(w.hourly_rate), 0) AS max_hourly_rate,
    COUNT(DISTINCT r.id) AS total_reservations
FROM categories c
LEFT JOIN workers w ON c.id = w.category_id
LEFT JOIN reservations r ON w.id = r.worker_id
GROUP BY c.id, c.name, c.slug;

-- dzienne rezerwacje
CREATE OR REPLACE VIEW v_daily_reservations AS
SELECT 
    r.reservation_date,
    COUNT(*) AS total_reservations,
    COUNT(CASE WHEN r.status = 'pending' THEN 1 END) AS pending_count,
    COUNT(CASE WHEN r.status = 'confirmed' THEN 1 END) AS confirmed_count,
    COUNT(CASE WHEN r.status = 'completed' THEN 1 END) AS completed_count,
    COUNT(CASE WHEN r.status = 'cancelled' THEN 1 END) AS cancelled_count
FROM reservations r
GROUP BY r.reservation_date
ORDER BY r.reservation_date DESC;

-- funkcje

-- oblicza srednia ocene fachowca
CREATE OR REPLACE FUNCTION calculate_worker_rating(p_worker_id INTEGER)
RETURNS DECIMAL(2,1) AS $$
DECLARE
    avg_rating DECIMAL(2,1);
BEGIN
    SELECT COALESCE(ROUND(AVG(rating)::numeric, 1), 0)
    INTO avg_rating
    FROM reviews
    WHERE worker_id = p_worker_id;
    
    RETURN avg_rating;
END;
$$ LANGUAGE plpgsql;

-- zlicza recenzje fachowca
CREATE OR REPLACE FUNCTION count_worker_reviews(p_worker_id INTEGER)
RETURNS INTEGER AS $$
DECLARE
    review_count INTEGER;
BEGIN
    SELECT COUNT(*)
    INTO review_count
    FROM reviews
    WHERE worker_id = p_worker_id;
    
    RETURN review_count;
END;
$$ LANGUAGE plpgsql;

-- sprawdza dostepnosc fachowca w danym terminie
CREATE OR REPLACE FUNCTION check_worker_availability(
    p_worker_id INTEGER,
    p_date DATE,
    p_time TIME
)
RETURNS BOOLEAN AS $$
DECLARE
    is_available BOOLEAN;
BEGIN
    SELECT NOT EXISTS(
        SELECT 1 FROM reservations
        WHERE worker_id = p_worker_id
        AND reservation_date = p_date
        AND reservation_time = p_time
        AND status IN ('pending', 'confirmed')
    ) INTO is_available;
    
    RETURN is_available;
END;
$$ LANGUAGE plpgsql;

-- sprawdza czy uzytkownik moze dodac recenzje
CREATE OR REPLACE FUNCTION can_add_review(
    p_reservation_id INTEGER,
    p_client_id INTEGER
)
RETURNS BOOLEAN AS $$
DECLARE
    can_review BOOLEAN;
BEGIN
    SELECT EXISTS(
        SELECT 1 FROM reservations r
        WHERE r.id = p_reservation_id
        AND r.client_id = p_client_id
        AND r.status = 'completed'
        AND NOT EXISTS(
            SELECT 1 FROM reviews rv
            WHERE rv.reservation_id = p_reservation_id
        )
    ) INTO can_review;
    
    RETURN can_review;
END;
$$ LANGUAGE plpgsql;

-- zwraca liczbe nieudanych prob logowania dla danego emaila
CREATE OR REPLACE FUNCTION get_failed_login_attempts(
    p_email VARCHAR(255),
    p_minutes INTEGER DEFAULT 15
)
RETURNS INTEGER AS $$
DECLARE
    attempt_count INTEGER;
BEGIN
    SELECT COUNT(*)
    INTO attempt_count
    FROM login_attempts
    WHERE email = p_email
    AND success = FALSE
    AND attempted_at > NOW() - (p_minutes || ' minutes')::INTERVAL;
    
    RETURN attempt_count;
END;
$$ LANGUAGE plpgsql;

-- generuje podsumowanie statystyk fachowca
CREATE OR REPLACE FUNCTION get_worker_summary(p_worker_id INTEGER)
RETURNS TABLE(
    total_reservations BIGINT,
    completed_reservations BIGINT,
    pending_reservations BIGINT,
    total_reviews BIGINT,
    avg_rating DECIMAL(2,1),
    total_earnings DECIMAL(10,2)
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        COUNT(DISTINCT r.id) AS total_reservations,
        COUNT(DISTINCT CASE WHEN r.status = 'completed' THEN r.id END) AS completed_reservations,
        COUNT(DISTINCT CASE WHEN r.status = 'pending' THEN r.id END) AS pending_reservations,
        COUNT(DISTINCT rv.id) AS total_reviews,
        COALESCE(ROUND(AVG(rv.rating)::numeric, 1), 0::numeric)::DECIMAL(2,1) AS avg_rating,
        COALESCE(SUM(CASE WHEN r.status = 'completed' THEN s.price ELSE 0 END), 0)::DECIMAL(10,2) AS total_earnings
    FROM workers w
    LEFT JOIN reservations r ON w.id = r.worker_id
    LEFT JOIN reviews rv ON w.id = rv.worker_id
    LEFT JOIN services s ON r.service_id = s.id
    WHERE w.id = p_worker_id
    GROUP BY w.id;
END;
$$ LANGUAGE plpgsql;

-- wyzwalacze

-- automatyczna aktualizacja ratingu i liczby recenzji po dodaniu recenzji
CREATE OR REPLACE FUNCTION update_worker_rating_on_review()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE workers
    SET 
        rating = calculate_worker_rating(NEW.worker_id),
        reviews_count = count_worker_reviews(NEW.worker_id)
    WHERE id = NEW.worker_id;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_rating_after_review ON reviews;
CREATE TRIGGER trg_update_rating_after_review
    AFTER INSERT ON reviews
    FOR EACH ROW
    EXECUTE FUNCTION update_worker_rating_on_review();

-- aktualizacja ratingu po usunieciu recenzji
CREATE OR REPLACE FUNCTION update_worker_rating_on_review_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE workers
    SET 
        rating = calculate_worker_rating(OLD.worker_id),
        reviews_count = count_worker_reviews(OLD.worker_id)
    WHERE id = OLD.worker_id;
    
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_rating_after_review_delete ON reviews;
CREATE TRIGGER trg_update_rating_after_review_delete
    AFTER DELETE ON reviews
    FOR EACH ROW
    EXECUTE FUNCTION update_worker_rating_on_review_delete();

-- automatyczna aktualizacja updated_at przy zmianie rezerwacji
CREATE OR REPLACE FUNCTION update_reservation_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_reservation_updated ON reservations;
CREATE TRIGGER trg_reservation_updated
    BEFORE UPDATE ON reservations
    FOR EACH ROW
    EXECUTE FUNCTION update_reservation_timestamp();

-- walidacja daty rezerwacji
CREATE OR REPLACE FUNCTION validate_reservation_date()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.reservation_date < CURRENT_DATE THEN
        RAISE EXCEPTION 'Nie można utworzyć rezerwacji na datę w przeszłości';
    END IF;
    
    IF NEW.reservation_date = CURRENT_DATE AND NEW.reservation_time < CURRENT_TIME THEN
        RAISE EXCEPTION 'Nie można utworzyć rezerwacji na godzinę w przeszłości';
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_validate_reservation_date ON reservations;
CREATE TRIGGER trg_validate_reservation_date
    BEFORE INSERT ON reservations
    FOR EACH ROW
    EXECUTE FUNCTION validate_reservation_date();

-- zapobieganie podwojnej rezerwacji
CREATE OR REPLACE FUNCTION prevent_double_booking()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS(
        SELECT 1 FROM reservations
        WHERE worker_id = NEW.worker_id
        AND reservation_date = NEW.reservation_date
        AND reservation_time = NEW.reservation_time
        AND status IN ('pending', 'confirmed')
        AND id != COALESCE(NEW.id, 0)
    ) THEN
        RAISE EXCEPTION 'Ten termin jest już zarezerwowany';
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_prevent_double_booking ON reservations;
CREATE TRIGGER trg_prevent_double_booking
    BEFORE INSERT OR UPDATE ON reservations
    FOR EACH ROW
    EXECUTE FUNCTION prevent_double_booking();

-- zapobieganie samodzielnej rezerwacji
CREATE OR REPLACE FUNCTION prevent_self_booking()
RETURNS TRIGGER AS $$
DECLARE
    worker_user_id INTEGER;
BEGIN
    SELECT user_id INTO worker_user_id
    FROM workers
    WHERE id = NEW.worker_id;
    
    IF worker_user_id = NEW.client_id THEN
        RAISE EXCEPTION 'Nie możesz zarezerwować wizyty u siebie';
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_prevent_self_booking ON reservations;
CREATE TRIGGER trg_prevent_self_booking
    BEFORE INSERT ON reservations
    FOR EACH ROW
    EXECUTE FUNCTION prevent_self_booking();

-- logowanie zmian statusu rezerwacji
CREATE TABLE IF NOT EXISTS reservation_status_log (
    id SERIAL PRIMARY KEY,
    reservation_id INTEGER REFERENCES reservations(id) ON DELETE CASCADE,
    old_status VARCHAR(20),
    new_status VARCHAR(20),
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE FUNCTION log_reservation_status_change()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.status IS DISTINCT FROM NEW.status THEN
        INSERT INTO reservation_status_log (reservation_id, old_status, new_status)
        VALUES (NEW.id, OLD.status, NEW.status);
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_log_status_change ON reservations;
CREATE TRIGGER trg_log_status_change
    AFTER UPDATE ON reservations
    FOR EACH ROW
    EXECUTE FUNCTION log_reservation_status_change();

INSERT INTO categories (name, slug, icon) VALUES
    ('Elektryka', 'elektryka', 'lightning.svg'),
    ('Hydraulika', 'hydraulika', 'plumbing.svg'),
    ('Sprzątanie', 'sprzatanie', 'cleaning.svg'),
    ('Malowanie', 'malowanie', 'painting.svg'),
    ('Montaż mebli', 'montaz-mebli', 'sofa.svg');
