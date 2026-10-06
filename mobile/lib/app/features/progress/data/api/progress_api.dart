import 'package:mobile/app/core/api/api_client.dart';
import 'package:mobile/app/features/progress/data/dto/progress_dto.dart';

class ProgressApi {
  final ApiClient _apiClient;

  ProgressApi(this._apiClient);

  Future<List<ProgressDto>> getList() async {
    final response = await _apiClient.client.get('/api/user/progress');
    final data = response.data as List<dynamic>;
    return data.map((json) => ProgressDto.fromJson(json as Map<String, dynamic>)).toList();
  }

  Future<ProgressDto> getProgress(String questId) async {
    final response = await _apiClient.client.get('/api/user/progress/$questId');
    return ProgressDto.fromJson(response.data as Map<String, dynamic>);
  }

  Future<ProgressDto> start(String questId) async {
    final response = await _apiClient.client.post('/api/user/progress/$questId/start');
    final parsed = _tryParseProgress(response.data);
    if (parsed != null) return parsed;
    // Сервер уже перевёл квест в active — дочитываем прогресс, если формат ответа неожиданный
    return getProgress(questId);
  }

  Future<ProgressDto> pause(String questId) async {
    final response = await _apiClient.client.patch('/api/user/progress/$questId/pause');
    final parsed = _tryParseProgress(response.data);
    if (parsed != null) return parsed;
    return getProgress(questId);
  }

  Future<void> abandon(String questId) async {
    await _apiClient.client.delete('/api/user/progress/$questId');
  }

  Future<StepCheckResponseDto> checkStep(String questId, double lat, double lng) async {
    final response = await _apiClient.client.post('/api/user/progress/$questId/check', data: {
      'lat': lat,
      'lng': lng,
    });
    return StepCheckResponseDto.fromJson(response.data as Map<String, dynamic>);
  }

  ProgressDto? _tryParseProgress(dynamic raw) {
    if (raw is! Map) return null;
    final map = Map<String, dynamic>.from(raw);
    final payload = map.containsKey('questId')
        ? map
        : (map['data'] is Map ? Map<String, dynamic>.from(map['data'] as Map) : null);
    if (payload == null || payload['questId'] is! String) return null;
    try {
      return ProgressDto.fromJson(payload);
    } catch (_) {
      return null;
    }
  }
}
