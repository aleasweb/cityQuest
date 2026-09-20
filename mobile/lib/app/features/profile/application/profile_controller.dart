import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:mobile/app/features/profile/domain/profile.dart';
import 'package:mobile/app/features/profile/domain/profile_repository.dart';
import 'package:mobile/app/features/auth/application/auth_controller.dart';

part 'profile_controller.g.dart';

@riverpod
ProfileRepository profileRepository(Ref ref) {
  throw UnimplementedError('Should be overridden in main');
}

@riverpod
class ProfileController extends _$ProfileController {
  @override
  FutureOr<Profile> build() async {
    final repository = ref.read(profileRepositoryProvider);
    return repository.getProfile();
  }

  Future<void> updateEmail(String email) async {
    state = const AsyncLoading();
    try {
      final repository = ref.read(profileRepositoryProvider);
      final profile = await repository.updateEmail(email);
      state = AsyncData(profile);
    } catch (e, st) {
      state = AsyncError(e, st);
    }
  }

  Future<void> logout() async {
    await ref.read(authControllerProvider.notifier).logout();
  }
}
