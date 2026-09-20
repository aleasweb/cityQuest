import 'package:freezed_annotation/freezed_annotation.dart';

part 'auth_failure.freezed.dart';

@freezed
class AuthFailure with _$AuthFailure {
  const factory AuthFailure.serverError(String message) = _ServerError;
  const factory AuthFailure.invalidCredentials(String message) = _InvalidCredentials;
  const factory AuthFailure.networkError() = _NetworkError;
  const factory AuthFailure.unknown(String message) = _Unknown;
}
