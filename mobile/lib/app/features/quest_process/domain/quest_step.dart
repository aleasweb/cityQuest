import 'package:freezed_annotation/freezed_annotation.dart';

part 'quest_step.freezed.dart';

@freezed
abstract class QuestStep with _$QuestStep {
  const factory QuestStep({
    required int stepNumber,
    required String title,
    required String description,
    required double targetLat,
    required double targetLng,
    required int radiusMeters,
    String? mediaUrl,
    String? mediaType,
  }) = _QuestStep;
}
