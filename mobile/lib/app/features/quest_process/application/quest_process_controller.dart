import 'package:geolocator/geolocator.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:mobile/app/features/quests/application/quest_controller.dart';
import 'package:mobile/app/features/progress/application/progress_controller.dart';
import 'package:mobile/app/features/quest_process/domain/quest_process_repository.dart';
import 'package:mobile/app/features/quest_process/application/quest_process_state.dart';
import 'package:mobile/app/features/quest_process/domain/quest_step.dart';

part 'quest_process_controller.g.dart';

@riverpod
QuestProcessRepository questProcessRepository(Ref ref) {
  // In a real app, ApiClient would be provided by a provider.
  // Here we assume ApiClient is available or we can just create it.
  // Wait, ApiClient is created asynchronously in main.dart.
  throw UnimplementedError('Should be overridden in main.dart or use a provider');
}

@riverpod
class QuestProcessController extends _$QuestProcessController {
  @override
  FutureOr<QuestProcessState> build(String questId) async {
    return _loadInitialData(questId);
  }

  Future<QuestProcessState> _loadInitialData(String questId) async {
    final questRepo = ref.read(questRepositoryProvider);
    final progressRepo = ref.read(progressRepositoryProvider);
    final processRepo = ref.read(questProcessRepositoryProvider);

    final quest = await questRepo.getQuestById(questId);
    final progress = await progressRepo.getProgress(questId);

    final steps = <QuestStep>[];
    for (var i = 1; i <= progress.currentStepNumber; i++) {
      try {
        final step = await processRepo.getStep(questId, i);
        steps.add(step);
      } catch (_) {
        // Ignore errors for past steps if they cannot be loaded
      }
    }

    return QuestProcessState(
      quest: quest,
      steps: steps,
      currentStepNumber: progress.currentStepNumber,
      totalSteps: progress.totalSteps,
      isCompleted: progress.status == 'COMPLETED',
    );
  }

  Future<void> checkLocation() async {
    if (state.value == null || state.value!.isCompleted) return;

    state = AsyncData(state.value!.copyWith(
      isCheckingLocation: true,
      locationCheckError: null,
      locationCheckSuccessMessage: null,
      distanceToTarget: null,
    ));

    try {
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        throw Exception('Службы геолокации отключены.');
      }

      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          throw Exception('Доступ к геолокации запрещен.');
        }
      }

      if (permission == LocationPermission.deniedForever) {
        throw Exception('Доступ к геолокации запрещен навсегда.');
      }

      final position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );

      final processRepo = ref.read(questProcessRepositoryProvider);
      final result = await processRepo.checkStep(questId, position.latitude, position.longitude);

      if (result.success) {
        final nextStepNumber = result.nextStepNumber;
        final isCompleted = result.isQuestCompleted ?? false;
        
        final steps = List<QuestStep>.from(state.value!.steps);
        if (!isCompleted && nextStepNumber != null) {
          final nextStep = await processRepo.getStep(questId, nextStepNumber);
          steps.add(nextStep);
        }

        state = AsyncData(state.value!.copyWith(
          isCheckingLocation: false,
          locationCheckSuccessMessage: result.message ?? 'Вы достигли цели!',
          currentStepNumber: nextStepNumber ?? state.value!.currentStepNumber,
          isCompleted: isCompleted,
          steps: steps,
        ));
      } else {
        state = AsyncData(state.value!.copyWith(
          isCheckingLocation: false,
          locationCheckError: result.message ?? 'Вы еще не на месте.',
          distanceToTarget: result.distance,
        ));
      }
    } catch (e) {
      state = AsyncData(state.value!.copyWith(
        isCheckingLocation: false,
        locationCheckError: e.toString(),
      ));
    }
  }

  Future<void> pauseQuest() async {
    try {
      final progressRepo = ref.read(progressRepositoryProvider);
      await progressRepo.pause(questId);
    } catch (e) {
      // Handle error
    }
  }
}
