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

  Future<void> _waitForInitialBuild() async {
    try {
      await future;
    } catch (_) {}
  }

  Future<void> start() async {
    await _waitForInitialBuild();
    if (!ref.mounted) return;
    state = const AsyncLoading();
    try {
      final repository = ref.read(progressRepositoryProvider);
      final progress = await repository.start(questId);
      if (!ref.mounted) return;
      state = AsyncData(progress);
    } catch (e, st) {
      if (!ref.mounted) return;
      state = AsyncError(e, st);
    }
  }

  Future<void> pause() async {
    await _waitForInitialBuild();
    if (!ref.mounted) return;
    state = const AsyncLoading();
    try {
      final repository = ref.read(progressRepositoryProvider);
      final progress = await repository.pause(questId);
      if (!ref.mounted) return;
      state = AsyncData(progress);
    } catch (e, st) {
      if (!ref.mounted) return;
      state = AsyncError(e, st);
    }
  }

  Future<void> abandon() async {
    await _waitForInitialBuild();
    if (!ref.mounted) return;
    state = const AsyncLoading();
    try {
      final repository = ref.read(progressRepositoryProvider);
      await repository.abandon(questId);
      if (!ref.mounted) return;
      state = const AsyncData(null);
    } catch (e, st) {
      if (!ref.mounted) return;
      state = AsyncError(e, st);
    }
  }

  Future<StepCheckResult> checkCurrentStep() async {
    final repository = ref.read(progressRepositoryProvider);
    final pos = await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high);
    if (!ref.mounted) {
      return const StepCheckResult(success: false, message: 'Операция прервана');
    }

    final result = await repository.checkStep(questId, pos.latitude, pos.longitude);
    if (!ref.mounted) {
      return result;
    }

    if (result.success) {
      final progress = await repository.getProgress(questId);
      if (!ref.mounted) return result;
      state = AsyncData(progress);
    }

    return result;
  }
}

@riverpod
class ProgressListController extends _$ProgressListController {
  @override
  FutureOr<List<Progress>> build() async {
    final repository = ref.read(progressRepositoryProvider);
    try {
      return await repository.getList();
    } catch (_) {
      return [];
    }
  }

  Future<void> refresh() async {
    state = const AsyncLoading();
    try {
      final repository = ref.read(progressRepositoryProvider);
      final list = await repository.getList();
      if (!ref.mounted) return;
      state = AsyncData(list);
    } catch (e, st) {
      if (!ref.mounted) return;
      state = AsyncError(e, st);
    }
  }
}
