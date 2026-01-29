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

INSERT INTO categories (name, slug, icon) VALUES
    ('Elektryka', 'elektryka', 'lightning.svg'),
    ('Hydraulika', 'hydraulika', 'plumbing.svg'),
    ('Sprzątanie', 'sprzatanie', 'cleaning.svg'),
    ('Malowanie', 'malowanie', 'painting.svg'),
    ('Montaż mebli', 'montaz-mebli', 'sofa.svg');
