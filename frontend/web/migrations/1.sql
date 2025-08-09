
CREATE TABLE quests (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
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
('Золотое кольцо мини', 'Компактный квест по достопримечательностям в стиле Золотого кольца России.', 'Владимир', 'сложные', 240, 8.5, 'https://images.unsplash.com/photo-1511593358241-7eea1f3c84e5?w=800', 'Ольга Смирнова', 203, TRUE);
