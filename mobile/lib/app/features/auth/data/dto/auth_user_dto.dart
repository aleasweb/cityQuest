import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:mobile/app/features/auth/domain/user.dart';

part 'auth_user_dto.freezed.dart';
part 'auth_user_dto.g.dart';

@freezed
abstract class AuthUserDto with _$AuthUserDto {
  const AuthUserDto._();

  const factory AuthUserDto({
    required String id,
    required String username,
    required String email,
    DateTime? createdAt,
  }) = _AuthUserDto;

  factory AuthUserDto.fromJson(Map<String, dynamic> json) => _$AuthUserDtoFromJson(json);

  AuthUser toDomain() => AuthUser(
        id: id,
        username: username,
        email: email,
        createdAt: createdAt,
      );
}
