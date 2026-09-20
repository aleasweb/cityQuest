import 'package:dio/dio.dart';
import 'package:mobile/app/core/api/api_client.dart';
import 'package:mobile/app/features/auth/data/dto/auth_user_dto.dart';
import 'package:mobile/app/features/auth/data/dto/login_response_dto.dart';

class AuthApi {
  final ApiClient _apiClient;

  AuthApi(this._apiClient);

  Future<LoginResponseDto> login(String username, String password) async {
    final response = await _apiClient.client.post('/api/auth/login', data: {
      'username': username,
      'password': password,
    });
    return LoginResponseDto.fromJson(response.data as Map<String, dynamic>);
  }

  Future<AuthUserDto> register(String username, String email, String password) async {
    final response = await _apiClient.client.post('/api/auth/register', data: {
      'username': username,
      'email': email,
      'password': password,
    });
    final data = response.data as Map<String, dynamic>;
    if (data.containsKey('user')) {
      return AuthUserDto.fromJson(data['user'] as Map<String, dynamic>);
    }
    return AuthUserDto.fromJson(data);
  }

  Future<void> logout() async {
    await _apiClient.client.post('/api/auth/logout');
    await _apiClient.clearCookies();
  }

  Future<AuthUserDto> me() async {
    final response = await _apiClient.client.get('/api/auth/me');
    final data = response.data as Map<String, dynamic>;
    if (data.containsKey('user')) {
      return AuthUserDto.fromJson(data['user'] as Map<String, dynamic>);
    }
    return AuthUserDto.fromJson(data);
  }
}
