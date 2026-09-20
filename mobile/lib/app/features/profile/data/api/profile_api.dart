import 'package:mobile/app/core/api/api_client.dart';
import 'package:mobile/app/features/profile/data/dto/profile_dto.dart';

class ProfileApi {
  final ApiClient _apiClient;

  ProfileApi(this._apiClient);

  Future<ProfileDto> getProfile() async {
    final response = await _apiClient.client.get('/api/user/profile');
    return ProfileDto.fromJson(response.data as Map<String, dynamic>);
  }

  Future<Map<String, dynamic>> getProfileWithHistory(String username) async {
    final response = await _apiClient.client.get(
      '/api/users/$username',
      queryParameters: {'includeQuests': 'true'},
    );
    return response.data as Map<String, dynamic>;
  }

  Future<List<dynamic>> getProgressList() async {
    final response = await _apiClient.client.get('/api/user/progress');
    return response.data as List<dynamic>;
  }

  Future<ProfileDto> updateEmail(String email) async {
    final response = await _apiClient.client.patch('/api/user/profile', data: {
      'email': email,
    });
    final data = response.data as Map<String, dynamic>;
    if (data.containsKey('user')) {
      return ProfileDto.fromJson(data['user'] as Map<String, dynamic>);
    }
    return ProfileDto.fromJson(data);
  }
}
