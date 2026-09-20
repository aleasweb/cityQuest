import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:mobile/app/features/quests/domain/quest.dart';
import 'package:mobile/app/features/quests/domain/quest_difficulty.dart';

part 'quest_dto.g.dart';
part 'quest_dto.freezed.dart';

@freezed
abstract class QuestDto with _$QuestDto {
  const QuestDto._();

  const factory QuestDto({
    required String id,
    required String title,
    String? description,
    String? city,
    String? difficulty,
    int? durationMinutes,
    double? distanceKm,
    @JsonKey(name: 'latitude') double? startLat,
    @JsonKey(name: 'longitude') double? startLng,
    @JsonKey(name: 'author') String? authorName,
    @JsonKey(name: 'isLikedByCurrentUser') @Default(false) bool isLiked,
    @Default(0) int likesCount,
    String? imageUrl,
  }) = _QuestDto;

  factory QuestDto.fromJson(Map<String, dynamic> json) => _$QuestDtoFromJson(json);

  Quest toDomain() => Quest(
        id: id,
        title: title,
        description: description ?? '',
        city: city ?? '',
        difficulty: QuestDifficulty.fromString(difficulty ?? 'medium'),
        durationMinutes: durationMinutes ?? 0,
        distanceKm: distanceKm ?? 0.0,
        startLat: startLat ?? 0.0,
        startLng: startLng ?? 0.0,
        authorName: authorName ?? 'Unknown',
        isLiked: isLiked,
        likesCount: likesCount,
        imageUrl: imageUrl,
      );
}
