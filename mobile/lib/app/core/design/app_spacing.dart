class AppSpacing {
  AppSpacing._();

  // Базовая сетка 8px
  static const double s2 = 2.0;
  static const double s4 = 4.0;
  static const double s8 = 8.0;
  static const double s12 = 12.0;
  static const double s16 = 16.0;
  static const double s20 = 20.0;
  static const double s24 = 24.0;
  static const double s32 = 32.0;
  static const double s40 = 40.0;
  static const double s48 = 48.0;
  static const double s64 = 64.0;

  // Радиусы скруглений (Border Radius)
  /// Для маленьких элементов (теги, бейджи)
  static const double radiusSmall = 8.0;
  
  /// Для стандартных элементов (поля ввода, небольшие карточки)
  static const double radiusMedium = 16.0;
  
  /// Для больших карточек и модальных окон
  static const double radiusLarge = 24.0;
  
  /// Для полностью круглых кнопок
  static const double radiusPill = 100.0;
}
