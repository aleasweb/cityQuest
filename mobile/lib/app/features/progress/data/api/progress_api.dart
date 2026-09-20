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
    return ProgressDto.fromJson(response.data as Map<String, dynamic>);
  }

  Future<ProgressDto> pause(String questId) async {
    final response = await _apiClient.client.post('/api/user/progress/$questId/pause');
    return ProgressDto.fromJson(response.data as Map<String, dynamic>);
  }

  Future<ProgressDto> abandon(String questId) async {
    final response = await _apiClient.client.post('/api/user/progress/$questId/abandon');
    return ProgressDto.fromJson(response.data as Map<String, dynamic>);
  }

  Future<StepCheckResponseDto> checkStep(String questId, double lat, double lng) async {
    final response = await _apiClient.client.post('/api/user/progress/$questId/check', data: {
      'lat': lat,
      'lng': lng,
    });
    return StepCheckResponseDto.fromJson(response.data as Map<String, dynamic>);
  }
}
