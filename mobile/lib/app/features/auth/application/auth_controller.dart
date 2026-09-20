import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:mobile/app/features/auth/domain/user.dart';
import 'package:mobile/app/features/auth/domain/auth_repository.dart';
import 'package:mobile/app/features/auth/domain/auth_failure.dart';
import 'package:mobile/app/core/storage/cache_manager.dart';

part 'auth_controller.g.dart';

@riverpod
class AuthController extends _$AuthController {
  AuthRepository get _repository => ref.read(authRepositoryProvider);

  @override
  FutureOr<AuthUser?> build() async {
    try {
      return await _repository.checkSession();
    } on AuthFailure catch (failure) {
      return failure.map(
        invalidCredentials: (_) => null,
        networkError: (_) {
          throw failure;
        },
        serverError: (_) {
          throw failure;
        },
        unknown: (_) => null,
      );
    } catch (_) {
      return null;
    }
  }

  Future<void> login(String username, String password) async {
    state = const AsyncLoading();
    try {
      final result = await _repository.login(username, password);
      state = AsyncData(result.user);
    } catch (e, st) {
      state = AsyncError(e, st);
    }
  }

  Future<void> register(String username, String email, String password) async {
    state = const AsyncLoading();
    try {
      await _repository.register(username, email, password);
      // Автоматический вход после регистрации, чтобы получить jwt_token cookie
      final loginResult = await _repository.login(username, password);
      state = AsyncData(loginResult.user);
    } catch (e, st) {
      state = AsyncError(e, st);
    }
  }

  Future<void> logout() async {
    state = const AsyncLoading();
    try {
      await _repository.logout();
      await CacheManager.clearAll();
      state = const AsyncData(null);
    } catch (e, st) {
      state = AsyncError(e, st);
    }
  }
}

@riverpod
AuthRepository authRepository(Ref ref) {
  throw UnimplementedError('Should be overridden in main');
}
