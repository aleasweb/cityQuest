
-- Remove the added quests
DELETE FROM quests WHERE title IN ('Тайны Петроградской стороны', 'Московское метро: подземные дворцы', 'Казанский кремль и татарская слобода');

-- Revert Nevsky Prospect image
UPDATE quests SET image_url = 'https://images.unsplash.com/photo-1520637836862-4d197d17c93a?w=800' WHERE title = 'Невский проспект: главная артерия города';
