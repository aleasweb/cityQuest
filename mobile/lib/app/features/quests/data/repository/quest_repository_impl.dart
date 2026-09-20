import 'package:dio/dio.dart';
import 'package:mobile/app/features/quests/domain/quest.dart';
import 'package:mobile/app/features/quests/domain/quest_difficulty.dart';
import 'package:mobile/app/features/quests/domain/quest_repository.dart';
import 'package:mobile/app/features/quests/data/api/quest_api.dart';

class QuestRepositoryImpl implements QuestRepository {
  final QuestApi _api;

  QuestRepositoryImpl(this._api);

  @override
  Future<QuestListResult> getQuests({
    String? city,
    QuestDifficulty? difficulty,
    bool? isPopular,
    int page = 1,
    int limit = 20,
  }) async {
    try {
      return await _api.getQuests(
        city: city,
        difficulty: difficulty?.value,
        isPopular: isPopular,
        page: page,
        limit: limit,
      );
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  @override
  Future<Quest> getQuestById(String id) async {
    try {
      final dto = await _api.getQuestById(id);
      return dto.toDomain();
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  @override
  Future<void> toggleLike(String id) async {
    try {
      await _api.toggleLike(id);
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Exception _handleError(DioException e) {
    if (e.response?.statusCode == 404) {
      return Exception('Квест не найден');
    }
    return Exception(e.message ?? 'Ошибка загрузки квестов');
  }
}
