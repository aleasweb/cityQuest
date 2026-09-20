import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:mobile/app/features/auth/data/dto/auth_user_dto.dart';
import 'package:mobile/app/features/auth/domain/user.dart';

part 'login_response_dto.freezed.dart';
part 'login_response_dto.g.dart';

@freezed
abstract class LoginResponseDto with _$LoginResponseDto {
  const LoginResponseDto._();

  const factory LoginResponseDto({
    required AuthUserDto user,
    String? token,
  }) = _LoginResponseDto;

  factory LoginResponseDto.fromJson(Map<String, dynamic> json) => _$LoginResponseDtoFromJson(json);

  LoginResult toDomain() => LoginResult(
        user: user.toDomain(),
        token: token,
      );
}
