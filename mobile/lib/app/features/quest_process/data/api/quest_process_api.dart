import 'package:mobile/app/core/api/api_client.dart';
import 'package:mobile/app/features/progress/data/dto/progress_dto.dart';
import 'package:mobile/app/features/quest_process/data/dto/quest_step_dto.dart';

class QuestProcessApi {
  final ApiClient _apiClient;

  QuestProcessApi(this._apiClient);

  Future<StepCheckResponseDto> checkStep(String questId, double lat, double lng) async {
    final response = await _apiClient.client.post('/api/user/progress/$questId/check', data: {
      'lat': lat,
      'lng': lng,
    });
    return StepCheckResponseDto.fromJson(response.data as Map<String, dynamic>);
  }

  Future<QuestStepDto> getStep(String questId, int stepNumber) async {
    final response = await _apiClient.client.get('/api/quests/$questId/steps/$stepNumber');
    return QuestStepDto.fromJson(response.data as Map<String, dynamic>);
  }
}
