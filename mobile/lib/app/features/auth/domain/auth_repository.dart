import 'package:mobile/app/features/auth/domain/user.dart';

abstract class AuthRepository {
  Future<LoginResult> login(String username, String password);
  Future<AuthUser> register(String username, String email, String password);
  Future<void> logout();
  Future<AuthUser> checkSession();
}
