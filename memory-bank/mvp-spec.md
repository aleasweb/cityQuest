# **Техническое задание (ТЗ) на разработку системы квестов**

## **1. Общее описание проекта**
Разрабатывается система интерактивных квестов с привязкой к реальным локациям. Основные функции:
- Просмотр и поиск квестов
- Навигация по этапам квеста с использованием геолокации
- Система авторизации пользователей
- Лайки и рейтинг квестов

## **2. Backend (API на Symfony + PostgreSQL)**
- **Backend**: Symfony, PostgreSQL, PHP 8.3
- **Frontend**: React 18, Tailwind, Leaflet
- **Ios, Android**: Flutter
- **Инструменты**: Docker, Git, Swagger
- **Принципы**: Domain driven design, CQRS, структура файлов в [memory-bank/mvp-backend-structure.md]

### **2.1. Технические требования**
- **Язык разработки:** PHP 8.3
- **Фреймворк:** Symfony 6+
- **База данных:** PostgreSQL 14+
- **Аутентификация:** Symfony Security Bundle
- **Формат данных:** JSON
- **Документация:** OpenAPI 3.0

### **2.2. Структура базы данных**

#### **Основные таблицы:**
1. **`users`** - пользователи
   ```sql
   id SERIAL PRIMARY KEY,
   email VARCHAR(255) NOT NULL,
   password VARCHAR(255) NOT NULL,
   username VARCHAR(100)
   ```

2. **`quests`** - квесты
   ```sql
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
   ```

3. **`quest_steps`** - этапы квеста
   ```sql
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   quest_id INTEGER NOT NULL,
   title TEXT,
   text TEXT,
   image_url TEXT,
   audio_url TEXT,
   video_url TEXT,
   lat DECIMAL,
   lng DECIMAL,
   radius INTEGER,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   ```

4. **`user_quest_progress`** - прогресс пользователей
   ```sql
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   user_id TEXT NOT NULL,
   quest_id INTEGER NOT NULL,
   is_completed BOOLEAN DEFAULT FALSE,
   is_liked BOOLEAN DEFAULT FALSE,
   completed_at TIMESTAMP,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   UNIQUE(user_id, quest_id)
   ```

### **2.3. Public API Endpoints**

#### **Аутентификация**
- `POST /api/auth/register` - Регистрация
- `POST /api/auth/login` - Вход
- `POST /api/auth/logout` - Выход

#### **Квесты (публичные)**
- `GET /api/quests` - Список квестов
  - Параметры:
    - `city` - фильтр по городу (city)
    - `difficulty` - фильтр по сложности (difficulty)
    - `author` - фильтр по автору (author)
    - `is_popular` - только популярные квесты (is_popular = true)
    - `sort` - сортировка (created_at, likes_count, duration_minutes)
    - `limit`, `offset` - пагинация

- `GET /api/quests/nearby` - Квесты рядом
  - Параметры:
    - `lat`, `lng` - координаты
    - `radius` - радиус поиска (км)

- `GET /api/quests/{id}` - Детали квеста

#### **Требующие авторизации**
- `POST /api/quests/{id}/like` - Поставить/убрать лайк (обновляет is_liked в user_quest_progress)
- `GET /api/user/progress` - Прогресс пользователя (список записей из user_quest_progress)
- `POST /api/user/progress/{questId}/start` - Начать квест (создает запись в user_quest_progress)
- `PATCH /api/user/progress/{questId}/complete` - Завершить квест (is_completed = true, completed_at = now)

### **2.4. Staff API Endpoints**

#### **Создание квеста**

#### **Апрув/блокировка квеста**

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
  - Название (title)
  - Изображение (image_url)
  - Описание (description)
  - Город (city)
  - Лайки (♥ + likes_count)
  - Сложность (difficulty)
  - Время прохождения (duration_minutes)
  - Расстояние (distance_km)
  - Автор (author)
  - Популярность (is_popular)

#### **2. Страница квеста**
- Полное описание (description)
- Автор (author)
- Время прохождения (duration_minutes) и расстояние (distance_km)
- Количество лайков (likes_count)
- Сложность (difficulty)
- Город (city)
- Кнопка "Начать квест"
- Статус популярности (is_popular)

#### **3. Авторизация**
- Формы входа/регистрации
- Валидация полей

#### **4. Профиль**
- Аватар пользователя (avatar)
- Имя пользователя (name)
- Рейтинг 
- Количество пройденных квестов
- Отзывы о квестах

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
1. Мобильные приложения (Flutter)
2. Создание квестов (админка + пользовательский интерфейс)
3. Система достижений
4. Социальные функции (комментарии, рейтинги)

## **6. Этапы**
1. API (public) + тесты на API
   1.1. Профиль
   1.2. Регистрация, авторизация
   1.3. Квест, получение данных
   1.4. Квесты, получение списков
2. Frontend
3. Ios
4. Android
5. API (staff)+ тесты на API
   1.1. Создание квеста
   1.2. Апрув/блокировка квеста
