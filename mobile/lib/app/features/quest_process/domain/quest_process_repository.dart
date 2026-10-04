import 'package:mobile/app/features/progress/domain/progress.dart';
import 'package:mobile/app/features/quest_process/domain/quest_step.dart';

abstract class QuestProcessRepository {
  Future<StepCheckResult> checkStep(String questId, double lat, double lng);
  Future<QuestStep> getStep(String questId, int stepNumber);
}
