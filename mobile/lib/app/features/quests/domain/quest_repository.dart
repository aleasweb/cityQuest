import 'package:mobile/app/features/quests/domain/quest.dart';
import 'package:mobile/app/features/quests/domain/quest_difficulty.dart';

abstract class QuestRepository {
  Future<QuestListResult> getQuests({
    String? city,
    QuestDifficulty? difficulty,
    bool? isPopular,
    int page = 1,
    int limit = 20,
  });

  Future<Quest> getQuestById(String id);
  Future<void> toggleLike(String id);
}

const _sentinel = Object();

class QuestFilters {
  final String? city;
  final QuestDifficulty? difficulty;
  final bool? isPopular;

  const QuestFilters({this.city, this.difficulty, this.isPopular});

  QuestFilters copyWith({
    Object? city = _sentinel,
    Object? difficulty = _sentinel,
    Object? isPopular = _sentinel,
  }) {
    return QuestFilters(
      city: city == _sentinel ? this.city : city as String?,
      difficulty: difficulty == _sentinel ? this.difficulty : difficulty as QuestDifficulty?,
      isPopular: isPopular == _sentinel ? this.isPopular : isPopular as bool?,
    );
  }
}
