# Навык: Flutter Design System (CityQuest)

Этот навык обязателен к прочтению при создании любых UI-компонентов и экранов в Flutter-приложении CityQuest. Он описывает принципы использования единой дизайн-системы, основанной на макетах (`@design/design1.jpg`).

## Основные правила

1. **Никаких захардкоженных значений**:
   - 🚫 Не используйте `Colors.grey`, `Color(0xFF...)` напрямую в виджетах.
   - 🚫 Не используйте жесткие отступы `EdgeInsets.all(15)`.
   - 🚫 Не используйте `TextStyle(fontSize: 14)` напрямую.
   - ✅ **Всегда** используйте токены из `AppColors`, `AppSpacing`, `AppTextStyles` или `Theme.of(context)`.

2. **Импорты**:
   ```dart
   import 'package:city_quest/core/design/app_colors.dart';
   import 'package:city_quest/core/design/app_spacing.dart';
   import 'package:city_quest/core/design/app_text_styles.dart';
   ```
   *(Убедитесь, что пути соответствуют актуальной структуре вашего `app/core/design/`)*

## Цвета (`AppColors` & `Theme.of(context)`)

Дизайн построен на светлом, теплом фоне с яркими акцентами и чисто-белыми карточками.
- `Scaffold` автоматически использует `AppColors.backgroundLight` (теплый белый).
- `Card` автоматически использует `AppColors.surfaceLight` (чистый белый).
- **Главный акцентный цвет (кнопки, активные иконки)**: `AppColors.primary` (оранжевый `#ED8E34`).
- **Текст**: `Theme.of(context).textTheme` (использует `AppColors.textLight` / `textSecondaryLight`).

## Отступы и Скругления (`AppSpacing`)

Сетка построена на **8px** (2, 4, 8, 12, 16, 20, 24, 32...).

- **Кнопки (ElevatedButton, OutlinedButton)**, поля ввода и некоторые теги в дизайне имеют **полное скругление** (Pill / Stadium). Используйте `AppSpacing.radiusPill`.
- **Карточки (квесты, маршруты, профиль)** имеют большие скругления. Используйте `AppSpacing.radiusLarge` (24px).
- **Мелкие элементы (теги категорий)**: `AppSpacing.radiusSmall` (8px) или `radiusMedium` (16px).

*Пример контейнера в виде карточки:*
```dart
Container(
  padding: const EdgeInsets.all(AppSpacing.s16),
  decoration: BoxDecoration(
    color: AppColors.surfaceLight,
    borderRadius: BorderRadius.circular(AppSpacing.radiusLarge),
  ),
  child: ...
)
```
*(Или просто используйте виджет `Card()` — он уже настроен в `AppTheme`)*.

## Типографика (`AppTextStyles` & `Theme.of(context)`)

Используется шрифт `Inter`. Старайтесь брать стили из темы.

- **h1** (`headlineLarge`): Крупные заголовки экранов (28px).
- **h2** (`headlineMedium`): Названия квестов, секций (20px).
- **h3** (`titleLarge`): Подзаголовки (18px).
- **bodyLarge** (`bodyLarge`): Крупный текст (16px).
- **body** (`bodyMedium`): Основной текст, описания (14px).
- **bodySmall** (`bodySmall`): Мелкий текст, статусы, даты (12px).
- **button** (`labelLarge`): Текст кнопок (16px, SemiBold).

*Пример:*
```dart
Text(
  'Название квеста',
  style: Theme.of(context).textTheme.headlineMedium,
)
```

## Кнопки

Главные кнопки (например, "Продолжить", "Начать") — большие, оранжевые, полностью скругленные.
Просто используйте `ElevatedButton`:
```dart
ElevatedButton(
  onPressed: () {},
  child: const Text('Продолжить'),
)
```
*(Тема `elevatedButtonTheme` уже задает нужный цвет, высоту 56px и скругление `AppSpacing.radiusPill`)*.

Если кнопка вторичная (например, с белым фоном и серой/черной обводкой), используйте `OutlinedButton`.

## Поля ввода

Поисковая строка и другие поля ввода (TextField) настроены в теме `inputDecorationTheme` — они имеют светло-серый/белый фон без обводки, со скруглениями `radiusPill`.

```dart
TextField(
  decoration: const InputDecoration(
    hintText: 'Город, район или название',
    prefixIcon: Icon(Icons.search),
  ),
)
```

## Иконки и теги

В макете много тегов (например, "Легкий", "60 мин", "2,4 км"). Делайте их через `Container` или `Chip` со скруглением `radiusSmall` или `radiusMedium`, фоном `AppColors.primaryLight` (если оранжевый акцент) или `AppColors.surfaceLight` и соответствующим мелким текстом `bodySmall`.

## Верстка и расположение (Layout)

Опираясь на дизайн `@design/design1.jpg`, соблюдайте следующие правила компоновки экранов:

1. **Отступы от краев экрана (Padding)**:
   - Основной контент всегда должен иметь горизонтальные отступы от краев экрана. Стандартный отступ — `AppSpacing.s16` (16px) или `AppSpacing.s20` (20px).
   - Например: `Padding(padding: const EdgeInsets.symmetric(horizontal: AppSpacing.s16), ...)`

2. **Безопасные зоны (SafeArea)**:
   - Всегда оборачивайте основной контент в `SafeArea`, чтобы элементы не перекрывались "челкой" (notch) или системными индикаторами снизу, особенно если не используете стандартный `AppBar`.

3. **Вертикальные отступы между блоками**:
   - Между связанными элементами внутри блока (например, заголовок и текст описания) используйте отступ `AppSpacing.s8` или `AppSpacing.s12`.
   - Между крупными логическими блоками на экране (например, блок "Популярные квесты" и следующий за ним) используйте `AppSpacing.s24` или `AppSpacing.s32`.
   - Для отступов используйте `SizedBox(height: AppSpacing.s16)` вместо `Padding`, если это возможно, для чистоты кода.

4. **Списки и скроллинг (Scrollable Content)**:
   - Если контент экрана может не поместиться, используйте `SingleChildScrollView` или `ListView`.
   - Карточки квестов в горизонтальных списках должны иметь отступы между собой `AppSpacing.s12` или `AppSpacing.s16` (используйте `ListView.separated` или `SizedBox(width: ...)`).

5. **Позиционирование кнопок (Bottom Actions)**:
   - Главные кнопки действия на экране (например, "Продолжить") обычно фиксируются внизу.
   - Рекомендуется размещать их внутри `bottomNavigationBar` у `Scaffold` (если нужна фиксация) или в самом конце `SingleChildScrollView` с дополнительным `SizedBox` снизу.
   - Обязательно добавляйте безопасный отступ снизу `SafeArea(child: Padding(...))`.

## Welcome / Splash экраны

На welcome-экранах (логин, онбординг) используется **тёплый фон**, визуально совпадающий с иллюстрацией города, и **светлое обрамление** по краям.

- **Фон**: `AppColors.backgroundWarm` (`#F3F0EB`) — тёплый бежевый, сливается с иллюстрацией.
- **Края**: `AppColors.backgroundWarmEdge` (`#FDF9F8`) — светлая полоска ~20px по бокам через горизонтальный `LinearGradient`.
- **Не** использовать `Scaffold.backgroundColor` — вместо этого оборачивать `SafeArea` в `Container` с градиентом.

*Референс: `login_screen.dart` — паттерн `Container(decoration: BoxDecoration(gradient: ...))` + `SafeArea`.*

```dart
Scaffold(
  body: Container(
    decoration: const BoxDecoration(
      gradient: LinearGradient(
        begin: Alignment.centerLeft,
        end: Alignment.centerRight,
        colors: [
          AppColors.backgroundWarmEdge,
          AppColors.backgroundWarm,
          AppColors.backgroundWarm,
          AppColors.backgroundWarmEdge,
        ],
        stops: [0.0, 0.052, 0.948, 1.0],
      ),
    ),
    child: SafeArea(child: ...),
  ),
)
```

**Брендинг в хедере**: логотип + текст «CityQuest» в одном `Row`, текст — `AppTextStyles.h1` с `fontSize: 30`, цвет `AppColors.primary`.

## Иконки (Font Awesome)

Начиная с версии Flutter 3.13+, системный класс `IconData` стал `final` и не может быть унаследован. Пакеты, такие как `font_awesome_flutter` старых версий (10.x.x), при сборке вызывают ошибки:
`The class 'IconData' can't be extended outside of its library because it's a final class.`

Для предотвращения таких проблем:
1. **Всегда используйте `font_awesome_flutter: ^11.0.0`** (или новее) в `pubspec.yaml`.
2. Если вы передаете Font Awesome иконку в качестве аргумента, не используйте тип `IconData`. Используйте виджет `Widget` в сигнатуре метода и передавайте сам виджет `FaIcon(FontAwesomeIcons.xxx)`:
   - 🚫 Неправильно: `Widget _buildBtn(IconData icon) { return Icon(icon); } ... _buildBtn(FontAwesomeIcons.apple);`
   - ✅ Правильно: `Widget _buildBtn(Widget iconWidget) { return iconWidget; } ... _buildBtn(FaIcon(FontAwesomeIcons.apple));`
   - ✅ В обычных местах просто используйте `FaIcon`: `FaIcon(FontAwesomeIcons.google, color: Colors.black)`

## Карточка квеста (`QuestCard`)

При отображении карточек квестов в списках придерживайтесь следующих визуальных правил, чтобы они соответствовали референс-дизайну:
1. **Пропорции и Фон**: Карточка имеет скругленные углы (`AppSpacing.radiusMedium`), фиксированную высоту (например, 180px) и включает полноразмерную фоновую картинку (`Image.network` + `BoxFit.cover`), занимающую всё пространство.
2. **Glassmorphism-плашка**: 
   - Информация на карточке располагается снизу поверх изображения.
   - Она должна быть обернута в `BackdropFilter` с эффектом размытия (`ImageFilter.blur(sigmaX: 8.0, sigmaY: 8.0)`).
   - Под размытием располагается `Container` с полупрозрачным тёмным фоном (`Colors.black.withAlpha(120)`) для обеспечения читаемости белого текста на любом фоне.
   - Отступы внутри плашки должны быть компактными: `padding: EdgeInsets.symmetric(horizontal: AppSpacing.s12, vertical: AppSpacing.s8)`.
3. **Текст и Индикаторы**:
   - Название квеста: `AppTextStyles.h3` (белый, `FontWeight.bold`, max 2 строки).
   - Город и время: `AppTextStyles.bodySmall` оранжевого цвета (`AppColors.primary`). Иконки рядом с ними (локация, часы) — 14px, оранжевые.
   - **Индикатор сложности**: Небольшой цветной кружок (`Container` с `BoxShape.circle`, 10x10 px) рядом с заголовком квеста (справа). Цвет кружка берется из `quest.difficulty.color`.
4. **Сетка и Отступы**: Расстояние между строкой заголовка и строкой с метаданными (город, время) должно быть минимальным (`AppSpacing.s4`).

## Блок фильтров (Filters)

Блок фильтров должен быть реализован единообразно на всех экранах, чтобы обеспечить интуитивно понятный пользовательский опыт:

1. **Компоновка**: Фильтры располагаются в горизонтальном списке (`SingleChildScrollView` с `scrollDirection: Axis.horizontal` или аналогичный виджет `AppFiltersRow`). Скролл должен быть плавным и уходить за край экрана (без обрезания полей отступа).
2. **Внешний вид чипов (`AppFilterChip`)**:
   - Используйте небольшие скругления (`AppSpacing.radiusPill` или `radiusMedium` в зависимости от формы).
   - Выбранное состояние должно визуально отличаться (например, темный/оранжевый фон и контрастный текст).
   - Невыбранное состояние — светлый или белый фон с легкой границей.
3. **Модальные окна (Bottom Sheets)**:
   - Для сложных фильтров (например, Город, Сложность) при клике на чип должно открываться модальное окно (`showModalBottomSheet`).
   - Внутри модального окна **НЕ должно быть** лишних заголовков (типа "Выберите город") или кнопок-крестиков для закрытия. Окно должно быть минималистичным и состоять только из списка опций (`ListTile`).
   - Окно должно быть обернуто в `SafeArea` и использовать `Column(mainAxisSize: MainAxisSize.min)`.
4. **Сброс фильтра**:
   - В каждом модальном окне фильтра **первой опцией** должен идти пункт для сброса (например, «Любая сложность», «Любой город»). 
   - Выбор этого пункта должен сбрасывать состояние фильтра (передавать `null` в провайдер) и обновлять соответствующий список данных.
   - На самом чипе в UI, если фильтр не выбран, должен отображаться этот же текст-фолбек (например, «Любой город» вместо «Все города»).
