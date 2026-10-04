import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:mobile/app/features/quests/domain/quest.dart';
import 'package:mobile/app/features/quest_process/domain/quest_step.dart';

part 'quest_process_state.freezed.dart';

@freezed
abstract class QuestProcessState with _$QuestProcessState {
  const factory QuestProcessState({
    Quest? quest,
    @Default([]) List<QuestStep> steps,
    @Default(1) int currentStepNumber,
    @Default(0) int totalSteps,
    @Default(false) bool isCheckingLocation,
    String? locationCheckError,
    String? locationCheckSuccessMessage,
    @Default(false) bool isCompleted,
    double? distanceToTarget,
  }) = _QuestProcessState;
}
