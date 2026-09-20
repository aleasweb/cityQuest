class AuthUser {
  final String id;
  final String username;
  final String email;
  final DateTime? createdAt;

  const AuthUser({
    required this.id,
    required this.username,
    required this.email,
    this.createdAt,
  });
}

class LoginResult {
  final AuthUser user;
  final String? token;

  const LoginResult({required this.user, this.token});
}
