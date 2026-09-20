class QuestHistoryItem {
  final String questId;
  final String title;
  final String? imageUrl;
  final String? difficulty;
  final String? city;
  final String status;
  final int? currentStepNumber;
  final int? totalSteps;

  const QuestHistoryItem({
    required this.questId,
    required this.title,
    this.imageUrl,
    this.difficulty,
    this.city,
    required this.status,
    this.currentStepNumber,
    this.totalSteps,
  });

  QuestHistoryItem copyWith({
    int? currentStepNumber,
    int? totalSteps,
  }) {
    return QuestHistoryItem(
      questId: questId,
      title: title,
      imageUrl: imageUrl,
      difficulty: difficulty,
      city: city,
      status: status,
      currentStepNumber: currentStepNumber ?? this.currentStepNumber,
      totalSteps: totalSteps ?? this.totalSteps,
    );
  }
}

class Profile {
  final String id;
  final String username;
  final String email;
  final String createdAt;
  final QuestHistoryItem? activeQuest;
  final List<QuestHistoryItem> pausedQuests;
  final List<QuestHistoryItem> completedQuests;

  const Profile({
    required this.id,
    required this.username,
    required this.email,
    required this.createdAt,
    this.activeQuest,
    this.pausedQuests = const [],
    this.completedQuests = const [],
  });
}
