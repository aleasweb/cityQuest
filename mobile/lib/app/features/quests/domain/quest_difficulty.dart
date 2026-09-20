import 'package:flutter/material.dart';
import 'package:mobile/app/core/design/app_colors.dart';

enum QuestDifficulty {
  easy('easy'),
  medium('medium'),
  hard('hard');

  final String value;
  const QuestDifficulty(this.value);

  factory QuestDifficulty.fromString(String value) {
    return QuestDifficulty.values.firstWhere(
      (e) => e.value == value.toLowerCase(),
      orElse: () => QuestDifficulty.medium, // Default fallback
    );
  }
}

extension QuestDifficultyUI on QuestDifficulty {
  String get label {
    return switch (this) {
      QuestDifficulty.easy => 'Легкий',
      QuestDifficulty.medium => 'Средний',
      QuestDifficulty.hard => 'Сложный',
    };
  }

  Color get color {
    return switch (this) {
      QuestDifficulty.easy => AppColors.success,
      QuestDifficulty.medium => AppColors.primary,
      QuestDifficulty.hard => AppColors.error,
    };
  }
}
