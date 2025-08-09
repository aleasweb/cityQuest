# **Техническое задание (ТЗ) на разработку системы квестов**

## **1. Общее описание проекта**
Разрабатывается система интерактивных квестов с привязкой к реальным локациям. Основные функции:
- Просмотр и поиск квестов
- Навигация по этапам квеста с использованием геолокации
- Система авторизации пользователей
- Лайки и рейтинг квестов
- Переключение цветовых тем (дневная/ночная)

## **2. Backend (API на Symfony + PostgreSQL)**

### **2.1. Технические требования**
- **Фреймворк:** Symfony 6+
- **База данных:** PostgreSQL 14+
- **Аутентификация:** Symfony Security Bundle
- **Формат данных:** JSON
- **Документация:** OpenAPI 3.0

### **2.2. Структура базы данных**

#### **Основные таблицы:**
1. **`quests`** - квесты
   ```sql
   id UUID PRIMARY KEY,
   title VARCHAR(100) NOT NULL,
   description TEXT,
   cover_image VARCHAR(255),
   city VARCHAR(50) NOT NULL,
   difficulty ENUM('easy','medium','hard') NOT NULL,
   estimated_time INT,
   likes_count INT DEFAULT 0,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP
   ```

2. **`quest_steps`** - этапы квестов
   ```sql
   id UUID PRIMARY KEY,
   quest_id UUID REFERENCES quests(id),
   title VARCHAR(100) NOT NULL,
   description TEXT,
   latitude DECIMAL(10,8) NOT NULL,
   longitude DECIMAL(11,8) NOT NULL,
   image VARCHAR(255),
   audio_clue VARCHAR(255),
   step_order INT NOT NULL
   ```

3. **`users`** - пользователи
   ```sql
   id UUID PRIMARY KEY,
   email VARCHAR(180) UNIQUE NOT NULL,
   password VARCHAR(255) NOT NULL,
   roles JSON NOT NULL,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   ```

4. **`user_quest_progress`** - прогресс пользователей
   ```sql
   id UUID PRIMARY KEY,
   user_id UUID REFERENCES users(id),
   quest_id UUID REFERENCES quests(id),
   current_step_id UUID REFERENCES quest_steps(id),
   is_completed BOOLEAN DEFAULT false,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   ```

5. **`quest_likes`** - лайки квестов
   ```sql
   user_id UUID REFERENCES users(id),
   quest_id UUID REFERENCES quests(id),
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (user_id, quest_id)
   ```

### **2.3. API Endpoints**

#### **Аутентификация**
- `POST /api/auth/register` - Регистрация
- `POST /api/auth/login` - Вход
- `POST /api/auth/logout` - Выход

#### **Квесты (публичные)**
- `GET /api/quests` - Список квестов
  - Параметры:
    - `city` - фильтр по городу
    - `difficulty` - фильтр по сложности
    - `sort` - сортировка (new, popular)
    - `limit`, `offset` - пагинация

- `GET /api/quests/nearby` - Квесты рядом
  - Параметры:
    - `lat`, `lng` - координаты
    - `radius` - радиус поиска (км)

- `GET /api/quests/{id}` - Детали квеста

#### **Требующие авторизации**
- `POST /api/quests/{id}/like` - Поставить/убрать лайк
- `GET /api/user/progress` - Прогресс пользователя
- `POST /api/user/progress/{questId}/start` - Начать квест
- `PATCH /api/user/progress/{questId}/update` - Обновить прогресс

## **3. Frontend (React)**

### **3.1. Технические требования**
- **React** 18+
- **React Router** 6+
- **State management:** Zustand
- **Стили:** Tailwind CSS
- **Карты:** react-leaflet (OpenStreetMap)
- **Интернационализация:** i18next

### **3.2. Структура приложения**

#### **1. Главная страница**
- Фильтры:
  - Выбор города (с определением местоположения)
  - Сложность (легкие/средние/сложные)
  - Сортировка (новые/популярные)
- Блоки:
  - Новые квесты
  - Популярные квесты
- Карточка квеста:
  - Название
  - Изображение
  - Название, город
  - Лайки (♥ + количество)
  - Сложность
  - Примерное время прохождения

#### **2. Страница квеста**
- Полное описание
- Автор
- Примерное время прохождения, расстояние
- Количество лайков
- Кнопка "Начать квест"

#### **3. Авторизация**
- Формы входа/регистрации
- Валидация полей

### **3.3. Дизайн-система**

#### **Цветовые темы**
**Дневная:**
- Основной: `#ffffff`
- Акцент: `#ed8e34`
- Текст: `#1f2937`

**Ночная:**
- Основной: `#1a1a1a`
- Акцент: `#3b1f16`
- Текст: `#e5e7eb`

#### **Компоненты**
- Кнопки (primary, secondary)
- Карточки квестов
- Фильтры
- Индикаторы сложности
- Модальные окна

## **4. Дополнительные требования**
- **Адаптивность:** Mobile-first
- **Производительность:** Lazy loading
- **Безопасность:** HTTPS
- **Логирование:** Основные события

## **5. Следующие этапы**
1. Мобильные приложения (React Native)
2. Создание квестов (админка + пользовательский интерфейс)
3. Система достижений
4. Социальные функции (комментарии, рейтинги)

## **6. Сроки и этапы**
1. **Неделя 1-2:** Базовый API (квесты, этапы)
2. **Неделя 3-4:** Авторизация, прогресс
3. **Неделя 5-6:** Главная страница
4. **Неделя 7-8:** Страницы квеста/этапа
5. **Неделя 9:** Тестирование, правки

**Технический стек:**
```
Backend: Symfony 6, PostgreSQL, PHP 8.1
Frontend: React 18, Tailwind, Leaflet
Инструменты: Docker, Git, Swagger
```