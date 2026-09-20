import 'package:dio/dio.dart';
import 'package:mobile/app/features/auth/domain/auth_repository.dart';
import 'package:mobile/app/features/auth/domain/auth_failure.dart';
import 'package:mobile/app/features/auth/domain/user.dart';
import 'package:mobile/app/features/auth/data/api/auth_api.dart';

class AuthRepositoryImpl implements AuthRepository {
  final AuthApi _api;

  AuthRepositoryImpl(this._api);

  @override
  Future<LoginResult> login(String username, String password) async {
    try {
      final response = await _api.login(username, password);
      return response.toDomain();
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        throw const AuthFailure.invalidCredentials('Неверное имя пользователя или пароль');
      }
      throw _handleDioError(e);
    } catch (e) {
      throw AuthFailure.unknown(e.toString());
    }
  }

  @override
  Future<AuthUser> register(String username, String email, String password) async {
    try {
      final dto = await _api.register(username, email, password);
      return dto.toDomain();
    } on DioException catch (e) {
      if (e.response?.statusCode == 400 || e.response?.statusCode == 422) {
        throw AuthFailure.invalidCredentials(e.message ?? 'Ошибка валидации');
      }
      throw _handleDioError(e);
    } catch (e) {
      throw AuthFailure.unknown(e.toString());
    }
  }

  @override
  Future<void> logout() async {
    try {
      await _api.logout();
    } on DioException catch (e) {
      throw _handleDioError(e);
    } catch (e) {
      throw AuthFailure.unknown(e.toString());
    }
  }

  @override
  Future<AuthUser> checkSession() async {
    try {
      final dto = await _api.me();
      return dto.toDomain();
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        throw const AuthFailure.invalidCredentials('Сессия истекла');
      }
      throw _handleDioError(e);
    } catch (e) {
      throw AuthFailure.unknown(e.toString());
    }
  }

  AuthFailure _handleDioError(DioException e) {
    if (e.type == DioExceptionType.connectionTimeout ||
        e.type == DioExceptionType.receiveTimeout ||
        e.type == DioExceptionType.connectionError) {
      return const AuthFailure.networkError();
    }
    return AuthFailure.serverError(e.message ?? 'Ошибка сервера');
  }
}
