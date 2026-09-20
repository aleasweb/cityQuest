import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:geolocator/geolocator.dart';
import 'package:mobile/app/features/progress/domain/progress.dart';
import 'package:mobile/app/features/progress/domain/progress_repository.dart';

part 'progress_controller.g.dart';

@riverpod
ProgressRepository progressRepository(Ref ref) {
  throw UnimplementedError('Should be overridden in main');
}

@riverpod
class ActiveProgressController extends _$ActiveProgressController {
  @override
  FutureOr<Progress?> build(String questId) async {
    final repository = ref.read(progressRepositoryProvider);
    try {
      return await repository.getProgress(questId);
    } catch (_) {
      return null;
    }
  }

  Future<void> start() async {
    state = const AsyncLoading();
    try {
      final repository = ref.read(progressRepositoryProvider);
      final progress = await repository.start(questId);
      state = AsyncData(progress);
    } catch (e, st) {
      state = AsyncError(e, st);
    }
  }

  Future<void> pause() async {
    state = const AsyncLoading();
    try {
      final repository = ref.read(progressRepositoryProvider);
      final progress = await repository.pause(questId);
      state = AsyncData(progress);
    } catch (e, st) {
      state = AsyncError(e, st);
    }
  }

  Future<void> abandon() async {
    state = const AsyncLoading();
    try {
      final repository = ref.read(progressRepositoryProvider);
      await repository.abandon(questId);
      state = const AsyncData(null);
    } catch (e, st) {
      state = AsyncError(e, st);
    }
  }

  Future<StepCheckResult> checkCurrentStep() async {
    final repository = ref.read(progressRepositoryProvider);
    final pos = await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high);

    final result = await repository.checkStep(questId, pos.latitude, pos.longitude);

    if (result.success) {
      final progress = await repository.getProgress(questId);
      state = AsyncData(progress);
    }

    return result;
  }
}
