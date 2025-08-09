
-- Add 3 more popular quests
INSERT INTO quests (title, description, city, difficulty, duration_minutes, distance_km, image_url, author, likes_count, is_popular) VALUES
('Тайны Петроградской стороны', 'Откройте для себя скрытые уголки и исторические загадки одного из самых атмосферных районов Санкт-Петербурга', 'Санкт-Петербург', 'средние', 90, 3.2, 'https://images.unsplash.com/photo-1571676165379-b64d66fd7ca1?w=800&h=600&fit=crop', 'Екатерина Волкова', 87, true),
('Московское метро: подземные дворцы', 'Путешествие по самым красивым станциям московского метрополитена с изучением их архитектурных особенностей', 'Москва', 'легкие', 120, 5.1, 'https://images.unsplash.com/photo-1517154421773-0529f29ea451?w=800&h=600&fit=crop', 'Дмитрий Соколов', 156, true),
('Казанский кремль и татарская слобода', 'Погружение в многовековую историю Казани через посещение древних памятников и национальных кварталов', 'Казань', 'сложные', 180, 4.8, 'https://images.unsplash.com/photo-1513977055326-8ae4e6aeb58d?w=800&h=600&fit=crop', 'Айгуль Хасанова', 92, true);

-- Update the Nevsky Prospect quest image
UPDATE quests SET image_url = 'https://images.unsplash.com/photo-1571676165379-b64d66fd7ca1?w=800&h=600&fit=crop' WHERE title = 'Невский проспект: главная артерия города';
