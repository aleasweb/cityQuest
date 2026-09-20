import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/app/features/auth/data/api/auth_api.dart';
import '../../../../helpers/test_api_client.dart';

void main() {
  group('AuthApi Tests', () {
    test('login should return parsed auth user response', () async {
      final loginResponse = {
        "data": {
          "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
          "user": {
            "id": "e81c1c9b-640a-429f-a0e2-808620245237",
            "email": "test@example.com",
            "username": "testuser",
            "createdAt": "2025-11-30 12:36:59"
          }
        }
      };

      final apiClient = TestApiClient.create({
        '/api/auth/login': loginResponse,
      });

      final authApi = AuthApi(apiClient);
      final result = await authApi.login('testuser', 'password123');

      expect(result.token, isNotNull);
      expect(result.user, isNotNull);
      expect(result.user?.username, "testuser");
    });

    test('me should return parsed current user', () async {
      final meResponse = {
        "data": {
          "user": {
            "id": "e81c1c9b-640a-429f-a0e2-808620245237",
            "email": "test@example.com",
            "username": "testuser",
            "createdAt": "2025-11-30 12:36:59"
          }
        }
      };

      final apiClient = TestApiClient.create({
        '/api/auth/me': meResponse,
      });

      final authApi = AuthApi(apiClient);
      final result = await authApi.me();

      expect(result.id, "e81c1c9b-640a-429f-a0e2-808620245237");
      expect(result.username, "testuser");
      expect(result.email, "test@example.com");
    });
  });
}
