-- PostgreSQL init script
-- Database will be created automatically from POSTGRES_DB env variable
-- 
-- ВАЖНО: При изменении схемы БД через Doctrine migrations,
-- не забудьте обновить этот файл соответствующим образом!
-- Этот скрипт используется для инициализации чистой БД в Docker.

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    roles JSON NOT NULL DEFAULT '["ROLE_USER"]',
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN users.id IS '(DC2Type:uuid)';
COMMENT ON COLUMN users.created_at IS '(DC2Type:datetime_immutable)';

CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users (email);
CREATE UNIQUE INDEX IF NOT EXISTS users_username_unique ON users (username);

-- Таблица квестов
CREATE TABLE IF NOT EXISTS quests (
    id UUID PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    city VARCHAR(100),
    difficulty VARCHAR(50),
    duration_minutes INT,
    distance_km DOUBLE PRECISION,
    image_url VARCHAR(500),
    author VARCHAR(255),
    likes_count INT DEFAULT 0 NOT NULL,
    is_popular BOOLEAN DEFAULT FALSE NOT NULL,
    latitude DOUBLE PRECISION,
    longitude DOUBLE PRECISION,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
);

COMMENT ON COLUMN quests.id IS '(DC2Type:uuid)';
COMMENT ON COLUMN quests.created_at IS '(DC2Type:datetime_immutable)';
COMMENT ON COLUMN quests.updated_at IS '(DC2Type:datetime_immutable)';

-- Индексы для оптимизации поиска
CREATE INDEX IF NOT EXISTS quests_city_idx ON quests (city);
CREATE INDEX IF NOT EXISTS quests_difficulty_idx ON quests (difficulty);
CREATE INDEX IF NOT EXISTS quests_is_popular_idx ON quests (is_popular);
CREATE INDEX IF NOT EXISTS quests_created_at_idx ON quests (created_at);
CREATE INDEX IF NOT EXISTS quests_geolocation_idx ON quests (latitude, longitude);

-- Таблица прогресса пользователей по квестам
CREATE TABLE IF NOT EXISTS user_quest_progress (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL,
    quest_id UUID NOT NULL,
    status VARCHAR(20) DEFAULT 'active' NOT NULL,
    completed_at TIMESTAMP(0) WITHOUT TIME ZONE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT fk_user_quest_progress_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_quest_progress_quest FOREIGN KEY (quest_id) REFERENCES quests(id) ON DELETE CASCADE,
    CONSTRAINT unique_user_quest UNIQUE (user_id, quest_id),
    CONSTRAINT check_status CHECK (status IN ('active', 'paused', 'completed'))
);

COMMENT ON COLUMN user_quest_progress.id IS '(DC2Type:uuid)';
COMMENT ON COLUMN user_quest_progress.user_id IS '(DC2Type:uuid)';
COMMENT ON COLUMN user_quest_progress.quest_id IS '(DC2Type:uuid)';
COMMENT ON COLUMN user_quest_progress.completed_at IS '(DC2Type:datetime_immutable)';
COMMENT ON COLUMN user_quest_progress.created_at IS '(DC2Type:datetime_immutable)';
COMMENT ON COLUMN user_quest_progress.updated_at IS '(DC2Type:datetime_immutable)';

-- Индексы для user_quest_progress
CREATE INDEX IF NOT EXISTS idx_user_status ON user_quest_progress(user_id, status);
CREATE INDEX IF NOT EXISTS idx_quest ON user_quest_progress(quest_id);

-- Таблица лайков квестов
CREATE TABLE IF NOT EXISTS quest_likes (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL,
    quest_id UUID NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT unique_user_quest_like UNIQUE (user_id, quest_id)
);

COMMENT ON COLUMN quest_likes.id IS '(DC2Type:uuid)';
COMMENT ON COLUMN quest_likes.user_id IS '(DC2Type:uuid)';
COMMENT ON COLUMN quest_likes.quest_id IS '(DC2Type:uuid)';
COMMENT ON COLUMN quest_likes.created_at IS '(DC2Type:datetime_immutable)';

-- Индексы для quest_likes
CREATE INDEX IF NOT EXISTS idx_quest_likes_user ON quest_likes(user_id);
CREATE INDEX IF NOT EXISTS idx_quest_likes_quest ON quest_likes(quest_id);
CREATE INDEX IF NOT EXISTS idx_quest_likes_created_at ON quest_likes(created_at);


INSERT INTO quests (
    id, title, description, city, difficulty, duration_minutes, distance_km, image_url,
    author, likes_count, is_popular, latitude, longitude, created_at, updated_at
) VALUES
      ('b4362704-891a-4e7f-850d-6be733124628', 'Вдоль по улице (часть 1)',
       'Ваша задача по фотографиям понять на какой улице находятся данные объекты. А затем найти сами объекты в любом порядке.\nВажно! Все объекты находятся на одной улице',
       'Penza', 'medium', 60, 4.0, '/s3/q1.png', 'aleas', 2, TRUE, 53.20166, 45.00564, '2025-11-30 12:36:59', '2025-11-30 12:36:59'),

      ('bbeee4d6-d112-46d8-983c-9b2dae6f24dc', 'Вдоль по улице (часть 2)',
       'Ваша задача по фотографиям понять на какой улице находятся данные объекты. А затем найти сами объекты в любом порядке.\nВажно! Все объекты находятся на одной улице',
       'Penza', 'medium', 50, 4.0, '/s3/q2.png', 'aleas', 0, TRUE, 53.20266, 45.00614, '2025-11-30 12:36:59', '2025-11-30 12:36:59'),

      ('2e90c723-8613-4121-8098-b52ba8fd5b8e', 'Пензенские силуэты',
       'Ваша задача по силуэту объекта с изображения понять, что это за объект в городе и найти его',
       'Penza', 'easy', 80, 5.0, '/s3/q3.png', 'aleas', 4, TRUE, 53.20366, 45.00664, '2025-11-30 12:36:59', '2025-11-30 12:36:59'),

      ('349ab46f-acab-4500-b421-97e83955bfbf', 'Внимание к деталям',
       'Ваша задача по фото детали объекта понять частью какого реального объекта она является и найти сам объект',
       'Penza', 'difficult', 100, 4.0, '/s3/q4.jpeg', 'aleas', 5, FALSE, 53.20466, 45.00764, '2025-11-30 12:36:59', '2025-11-30 12:36:59'),

      ('1889ea69-d48e-4ef0-9da8-f9028517a2e1', 'Старая Пенза',
       'Ваша задача по архивному фото понять что это за объект и найти место его расположения',
       'Penza', 'medium', 60, NULL, '/s3/q5.png', 'aleas', 3, TRUE, 53.20316, 45.00664, '2025-11-30 12:36:59', '2025-11-30 12:36:59'),

      ('3db76d13-a3f8-4719-8534-0e03132b72f4', 'Московский кремль',
       'Ваша задача по фото найти объекты на территории московского кремля',
       'Moscow', 'easy', 30, 2.0, '/s3/q6.webp', 'aleas', 5, FALSE, 55.752121, 37.617664, '2025-11-30 12:36:59', '2025-11-30 12:36:59');
