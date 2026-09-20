class Progress {
  final String questId;
  final String status;
  final int currentStepNumber;
  final int totalSteps;
  final DateTime startedAt;
  final DateTime? completedAt;

  const Progress({
    required this.questId,
    required this.status,
    required this.currentStepNumber,
    required this.totalSteps,
    required this.startedAt,
    this.completedAt,
  });
}

class StepCheckResult {
  final bool success;
  final String? message;
  final double? distance;
  final double? bearing;
  final int? nextStepNumber;
  final bool? isQuestCompleted;

  const StepCheckResult({
    required this.success,
    this.message,
    this.distance,
    this.bearing,
    this.nextStepNumber,
    this.isQuestCompleted,
  });
}
