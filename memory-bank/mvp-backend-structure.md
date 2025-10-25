В backend рекомендована следующая структура размещения файлов по DDD

src/
├── User/
    ├── Domain/
    │   ├── Entity/
    │   ├── Repository/          # Интерфейсы репозиториев
    │   └── Service/             # Доменные сервисы
    ├── Application/
    │   ├── DTO/                 # Объекты передачи данных
    │   └── Service/             # Сервисы приложения
    ├── Infrastructure/
    │   ├── Db/                  # Реализация репозиториев для DB
    │   └── Cache/               # Реализация репозиториев для кеша
    ├── Presentation/            
    │   ├── Controller/
    │   ├── Cli/
    │   └── View/