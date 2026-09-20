import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:mobile/app/features/profile/domain/profile.dart';

part 'profile_dto.g.dart';
part 'profile_dto.freezed.dart';

@freezed
abstract class QuestHistoryItemDto with _$QuestHistoryItemDto {
  const QuestHistoryItemDto._();

  const factory QuestHistoryItemDto({
    required Map<String, dynamic> quest,
    required String status,
    required String startedAt,
    String? completedAt,
  }) = _QuestHistoryItemDto;

  factory QuestHistoryItemDto.fromJson(Map<String, dynamic> json) => _$QuestHistoryItemDtoFromJson(json);

  QuestHistoryItem toDomain() => QuestHistoryItem(
        questId: quest['id'] as String,
        title: quest['title'] as String,
        imageUrl: quest['imageUrl'] as String?,
        difficulty: quest['difficulty'] as String?,
        city: quest['city'] as String?,
        status: status,
      );
}

@freezed
abstract class ProfileDto with _$ProfileDto {
  const ProfileDto._();

  const factory ProfileDto({
    required String id,
    required String username,
    required String email,
    required String createdAt,
    QuestHistoryItemDto? activeQuest,
    @Default([]) List<QuestHistoryItemDto> pausedQuests,
    @Default([]) List<QuestHistoryItemDto> completedQuests,
  }) = _ProfileDto;

  factory ProfileDto.fromJson(Map<String, dynamic> json) => _$ProfileDtoFromJson(json);

  Profile toDomain() => Profile(
        id: id,
        username: username,
        email: email,
        createdAt: createdAt,
        activeQuest: activeQuest?.toDomain(),
        pausedQuests: pausedQuests.map((q) => q.toDomain()).toList(),
        completedQuests: completedQuests.map((q) => q.toDomain()).toList(),
      );
}
