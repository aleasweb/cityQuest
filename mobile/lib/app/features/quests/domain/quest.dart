import 'package:mobile/app/features/quests/domain/quest_difficulty.dart';

class Quest {
  final String id;
  final String title;
  final String description;
  final String city;
  final QuestDifficulty difficulty;
  final int durationMinutes;
  final double distanceKm;
  final double startLat;
  final double startLng;
  final String authorName;
  final bool isLiked;
  final int likesCount;
  final String? imageUrl;

  const Quest({
    required this.id,
    required this.title,
    required this.description,
    required this.city,
    required this.difficulty,
    required this.durationMinutes,
    required this.distanceKm,
    required this.startLat,
    required this.startLng,
    required this.authorName,
    this.isLiked = false,
    this.likesCount = 0,
    this.imageUrl,
  });

  Quest copyWith({
    bool? isLiked,
    int? likesCount,
    String? imageUrl,
  }) {
    return Quest(
      id: id,
      title: title,
      description: description,
      city: city,
      difficulty: difficulty,
      durationMinutes: durationMinutes,
      distanceKm: distanceKm,
      startLat: startLat,
      startLng: startLng,
      authorName: authorName,
      isLiked: isLiked ?? this.isLiked,
      likesCount: likesCount ?? this.likesCount,
      imageUrl: imageUrl ?? this.imageUrl,
    );
  }
}

class QuestListResult {
  final List<Quest> items;
  final int total;

  const QuestListResult({required this.items, required this.total});
}
