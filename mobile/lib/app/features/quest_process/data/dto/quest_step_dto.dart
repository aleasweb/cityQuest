import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:mobile/app/features/quest_process/domain/quest_step.dart';

part 'quest_step_dto.g.dart';
part 'quest_step_dto.freezed.dart';

@freezed
abstract class QuestStepDto with _$QuestStepDto {
  const QuestStepDto._();

  const factory QuestStepDto({
    required int stepNumber,
    required String title,
    required String description,
    required double targetLat,
    required double targetLng,
    required int radiusMeters,
    String? mediaUrl,
    String? mediaType,
  }) = _QuestStepDto;

  factory QuestStepDto.fromJson(Map<String, dynamic> json) => _$QuestStepDtoFromJson(json);

  QuestStep toDomain() => QuestStep(
        stepNumber: stepNumber,
        title: title,
        description: description,
        targetLat: targetLat,
        targetLng: targetLng,
        radiusMeters: radiusMeters,
        mediaUrl: mediaUrl,
        mediaType: mediaType,
      );
}
