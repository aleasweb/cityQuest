import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:mobile/app/features/progress/domain/progress.dart';

part 'progress_dto.g.dart';
part 'progress_dto.freezed.dart';

@freezed
abstract class ProgressDto with _$ProgressDto {
  const ProgressDto._();

  const factory ProgressDto({
    required String questId,
    required String status,
    required int currentStepNumber,
    required int totalSteps,
    required String startedAt,
    String? completedAt,
  }) = _ProgressDto;

  factory ProgressDto.fromJson(Map<String, dynamic> json) => _$ProgressDtoFromJson(json);

  Progress toDomain() => Progress(
        questId: questId,
        status: status,
        currentStepNumber: currentStepNumber,
        totalSteps: totalSteps,
        startedAt: DateTime.parse(startedAt),
        completedAt: completedAt != null ? DateTime.parse(completedAt!) : null,
      );
}

@freezed
abstract class StepCheckResponseDto with _$StepCheckResponseDto {
  const StepCheckResponseDto._();

  const factory StepCheckResponseDto({
    required bool success,
    String? message,
    double? distance,
    double? bearing,
    int? nextStepNumber,
    bool? isQuestCompleted,
  }) = _StepCheckResponseDto;

  factory StepCheckResponseDto.fromJson(Map<String, dynamic> json) =>
      _$StepCheckResponseDtoFromJson(json);

  StepCheckResult toDomain() => StepCheckResult(
        success: success,
        message: message,
        distance: distance,
        bearing: bearing,
        nextStepNumber: nextStepNumber,
        isQuestCompleted: isQuestCompleted,
      );
}
