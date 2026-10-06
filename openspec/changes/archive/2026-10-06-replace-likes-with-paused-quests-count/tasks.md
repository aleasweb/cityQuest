## 1. Web (React)

- [x] 1.1 Обновить `UserProfile.tsx`: заменить иконку `Heart` на `PauseCircle` (или `Clock`), изменить логику подсчета с лайков на длину массива `profileData?.pausedQuests?.length || 0`, изменить текст "Понравилось" на "На паузе". Проверить визуально через `npm run dev`.

## 2. Mobile (Flutter)

- [x] 2.1 Обновить `ProfileScreen.dart`: в виджете `_ProfileContent` заменить стат-карточку `_StatCard(count: '0', label: 'В избранном')` на карточку для отложенных квестов: выводить `profile.pausedQuests.length.toString()` и label `'На паузе'`. Проверить сборку через `dart run build_runner build -d` и `flutter run`.

## 3. Documentation Sync

- [x] 3.1 Обновить `openspec/specs/user/architecture.md` (или `openspec/specs/mobile-app/architecture.md`), если требуется отразить изменение в UI (опционально, так как глобальная архитектура не меняется, но требуется по правилу).
- [x] 3.2 Добавить ссылки на UI тесты (если они появятся) или отметить, что задача проверена вручную, в дельта-спецификациях `specs/mobile-app/spec.md` и `specs/user/spec.md`.