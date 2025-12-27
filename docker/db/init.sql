CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS workers (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    category_id INTEGER REFERENCES categories(id),
    description TEXT,
    rating DECIMAL(2,1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (email, password, username) VALUES
    ('user1@example.com', 'password1', 'Jan Kowalski'),
    ('user2@example.com', 'password2', 'Anna Nowak');

INSERT INTO categories (name, icon) VALUES
    ('Elektryka', 'lightning.svg'),
    ('Hydraulika', 'plumbing.svg'),
    ('Sprzątanie', 'cleaning.svg'),
    ('Malowanie', 'painting.svg');