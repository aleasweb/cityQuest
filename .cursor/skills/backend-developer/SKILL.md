Эксперт по backend-разработке (Symfony, PHP). Используйте этот навык при написании и рефакторинге кода на стороне сервера.

## Правила безопасности и обработки ошибок в контроллерах

- **Никогда** не выводите текст исключений напрямую в ответах API.
Неправильно: `return $this->json(['error' => $e->getMessage()]);` 
Правильно: `'error' => 'Quest not found'`, `'error' => 'Invalid request format'`).

## Исключение из правил о зависимости Domain Layer от Infrastructure Layer

- Domain Layer — без зависимостей на Symfony/Doctrine (исключение: атрибуты `#[ORM\...]` в доменных сущностях).

## Правила наследования exception

- Domain exceptions наследуют `\DomainException`

## Правила импорта классов (use)

- Вместо указания полных путей (FQCN) в коде (например, `\App\User\Domain\Entity\User`), всегда выносите их в секцию `use`.
- Исключение: глобальные встроенные классы PHP (например, `\Exception`, `\DateTimeImmutable`, `\LogicException`) можно использовать с ведущим слешем без импорта.