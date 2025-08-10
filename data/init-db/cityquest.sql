-- PostgreSQL init script
-- Database will be created automatically from POSTGRES_DB env variableпше

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS quests (
    id SERIAL PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT,
    city TEXT,
    difficulty TEXT,
    duration_minutes INTEGER,
    distance_km REAL,
    image_url TEXT,
    author TEXT,
    likes_count INTEGER DEFAULT 0,
    is_popular BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO quests (title, description, city, difficulty, duration_minutes, distance_km, image_url, author, likes_count, is_popular) VALUES
('Исторический центр Москвы', 'Пройдите по следам великих событий российской истории через Красную площадь, Кремль и близлежащие улицы.', 'Москва', 'средние', 120, 3.5, 'https://images.unsplash.com/photo-1520637836862-4d197d17c63a?w=800', 'Анна Петрова', 247, TRUE),
('Тайны Питерских дворов', 'Откройте секреты парадных и дворов Санкт-Петербурга, где история оживает в каждом уголке.', 'Санкт-Петербург', 'сложные', 180, 4.2, 'https://images.unsplash.com/photo-1583048800985-d8e8b8b1f66d?w=800', 'Михаил Сидоров', 189, TRUE),
('Прогулка по Арбату', 'Легкий квест по самой известной пешеходной улице Москвы с множеством интересных фактов.', 'Москва', 'легкие', 60, 1.5, 'https://images.unsplash.com/photo-1520348264293-bb93b6b2572a?w=800', 'Елена Козлова', 156, FALSE),
('Казанский кремль', 'Изучите архитектуру и историю Казанского кремля, объекта всемирного наследия ЮНЕСКО.', 'Казань', 'средние', 90, 2.1, 'https://images.unsplash.com/photo-1546196104-d7deb2beeff7?w=800', 'Рустам Галиев', 98, FALSE),
('Ночной Екатеринбург', 'Вечерний квест по центру Екатеринбурга с посещением основных достопримечательностей.', 'Екатеринбург', 'легкие', 75, 2.8, 'https://images.unsplash.com/photo-1496436818536-e239445d3327?w=800', 'Дмитрий Волков', 67, FALSE),
('Золотое кольцо мини', 'Компактный квест по достопримечательностям в стиле Золотого кольца России.', 'Владимир', 'сложные', 240, 8.5, 'https://images.unsplash.com/photo-1511593358241-7eea1f3c84e5?w=800', 'Ольга Смирнова', 203, TRUE),
('Тайны Кремля', 'Откройте секреты главной крепости России. Путешествие по историческим залам и башням.', 'Москва', 'средние', 180, 2.5, 'https://images.unsplash.com/photo-1513326738677-b964603b136d?w=800', 'Историк Петров', 245, TRUE),
('Невский проспект: от дворца до дворца', 'Прогулка по главной улице Петербурга с посещением знаковых мест и дворцов.', 'Санкт-Петербург', 'легкие', 120, 3.2, 'https://images.unsplash.com/photo-1520637836862-4d197d17c35a?w=800', 'Гид Анна', 189, TRUE),
('Золотое кольцо в миниатюре', 'Знакомство с архитектурой древних русских городов прямо в центре столицы.', 'Москва', 'сложные', 240, 4.1, 'https://images.unsplash.com/photo-1547036967-23d11aacaee0?w=800', 'Краевед Иванов', 156, TRUE),
('Тайны Петроградской стороны', 'Откройте для себя скрытые уголки и исторические загадки одного из самых атмосферных районов Санкт-Петербурга', 'Санкт-Петербург', 'средние', 90, 3.2, 'https://images.unsplash.com/photo-1571676165379-b64d66fd7ca1?w=800&h=600&fit=crop', 'Екатерина Волкова', 87, true),
('Московское метро: подземные дворцы', 'Путешествие по самым красивым станциям московского метрополитена с изучением их архитектурных особенностей', 'Москва', 'легкие', 120, 5.1, 'https://images.unsplash.com/photo-1517154421773-0529f29ea451?w=800&h=600&fit=crop', 'Дмитрий Соколов', 156, true),
('Казанский кремль и татарская слобода', 'Погружение в многовековую историю Казани через посещение древних памятников и национальных кварталов', 'Казань', 'сложные', 180, 4.8, 'https://images.unsplash.com/photo-1513977055326-8ae4e6aeb58d?w=800&h=600&fit=crop', 'Айгуль Хасанова', 92, true);

CREATE TABLE user_quest_progress (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    quest_id INTEGER NOT NULL REFERENCES quests(id) ON DELETE CASCADE,
    is_completed BOOLEAN DEFAULT FALSE,
    is_liked BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, quest_id)
);