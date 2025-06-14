CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    chart_time_interval VARCHAR(10) DEFAULT '1mo'
);

CREATE TABLE IF NOT EXISTS user_favorites (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    symbol VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, symbol)
);

CREATE TABLE IF NOT EXISTS recently_viewed (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    symbol VARCHAR(20) NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, symbol)
);

CREATE TABLE IF NOT EXISTS stock_cache (
     id SERIAL PRIMARY KEY,
     uri VARCHAR(255) UNIQUE NOT NULL,
     data JSONB NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE INDEX IF NOT EXISTS idx_user_favorites_user_id ON user_favorites(user_id);
CREATE INDEX IF NOT EXISTS idx_user_favorites_symbol ON user_favorites(symbol);
CREATE INDEX IF NOT EXISTS idx_recently_viewed_user_id ON recently_viewed(user_id);
CREATE INDEX IF NOT EXISTS idx_recently_viewed_viewed_at ON recently_viewed(viewed_at);
CREATE INDEX IF NOT EXISTS idx_stock_cache_uri ON stock_cache(uri);
CREATE INDEX IF NOT EXISTS idx_stock_cache_created_at ON stock_cache(created_at);


CREATE OR REPLACE FUNCTION clean_old_cache_entries()
RETURNS void AS $$
BEGIN
    DELETE FROM stock_cache
    WHERE created_at < NOW() - INTERVAL '5 minutes';
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION trigger_clean_old_cache()
RETURNS TRIGGER AS $$
BEGIN
    PERFORM clean_old_cache_entries();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER trigger_cache_cleanup
    AFTER INSERT ON stock_cache
    FOR EACH STATEMENT
    EXECUTE FUNCTION trigger_clean_old_cache();

-- Insert default admin user (password: admin123) & abc user (password 123456)
INSERT INTO users (username, password, role) 
VALUES
    ('abc', '$2y$10$1jNzNAQJClJlxKjszhiQhOcofhiBVpWj4.beLT3pP6Rd1/YshLad.', 'user'),
    ('admin', '$2y$10$b6.kgTIuCs8ucyiQG2DzGO8116sTQmUks2ST3dvJ5/jGmXJ7HZGda', 'admin')
ON CONFLICT (username) DO NOTHING;