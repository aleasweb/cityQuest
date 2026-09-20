import 'package:mobile/app/core/api/api_client.dart';
import 'package:mobile/app/features/quests/data/dto/quest_dto.dart';
import 'package:mobile/app/features/quests/domain/quest.dart';

class QuestApi {
  final ApiClient _apiClient;

  QuestApi(this._apiClient);

  Future<QuestListResult> getQuests({
    String? city,
    String? difficulty,
    bool? isPopular,
    int page = 1,
    int limit = 20,
  }) async {
    final response = await _apiClient.client.get('/api/quests', queryParameters: {
      if (city != null) 'city': city,
      if (difficulty != null) 'difficulty': difficulty,
      if (isPopular != null) 'is_popular': isPopular,
      'page': page,
      'limit': limit,
    });

    final items = (response.data as List<dynamic>)
        .map((json) => QuestDto.fromJson(json as Map<String, dynamic>).toDomain())
        .toList();
    final meta = response.extra['meta'] as Map<String, dynamic>?;
    final total = meta?['total'] as int? ?? items.length;

    return QuestListResult(items: items, total: total);
  }

  Future<QuestDto> getQuestById(String id) async {
    final response = await _apiClient.client.get('/api/quests/$id');
    return QuestDto.fromJson(response.data as Map<String, dynamic>);
  }

  Future<void> toggleLike(String id) async {
    await _apiClient.client.post('/api/quests/$id/like');
  }
}
