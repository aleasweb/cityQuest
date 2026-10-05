## 1. Design System

- [x] 1.1 Создать виджет `ActiveQuestBlock` (stateless) в директории `mobile/lib/app/core/design_system/widgets/` и реализовать его визуальную часть (название квеста, прогресс, кнопка). Убедиться, что виджет корректно отображается при передаче моковых данных.
- [x] 1.2 При отсутствии данных в `ActiveQuestBlock` возвращать `SizedBox.shrink()`. Убедиться, что пустой виджет не занимает места в UI.

## 2. Integration with Main Screen

- [x] 2.1 Подключить `ActiveQuestBlock` на главный экран `mobile/lib/app/features/quest_catalog/presentation/quest_catalog_screen.dart` (над списком и фильтрами).
- [x] 2.2 Получать состояние активного квеста из глобального провайдера `UserProgress` (или использовать заглушку, если провайдер еще не до конца реализован, но структура должна быть ConsumerWidget/ref.watch).
- [x] 2.3 Привязать коллбэк виджета `onTap` к вызову навигации (например, `context.push('/quest_process/${questId}')`). Убедиться, что переход по нажатию работает.

## 3. Documentation & Verification Sync

- [x] 3.1 Обновить `openspec/specs/mobile-app/architecture.md`, добавив описание новых архитектурных решений (выделение ActiveQuestBlock).
- [x] 3.2 Добавить ссылки на новые тесты (или проверки) в соответствующие сценарии в `openspec/changes/add-active-quest-block/specs/design-system/active-quest-block-mobile/spec.md` и `mobile-app/spec.md`.
