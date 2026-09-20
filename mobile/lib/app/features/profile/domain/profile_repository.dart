import 'package:mobile/app/features/profile/domain/profile.dart';

abstract class ProfileRepository {
  Future<Profile> getProfile();
  Future<Profile> updateEmail(String email);
}
