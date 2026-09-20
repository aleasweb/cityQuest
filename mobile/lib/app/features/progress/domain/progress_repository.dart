import 'package:mobile/app/features/progress/domain/progress.dart';

abstract class ProgressRepository {
  Future<List<Progress>> getList();
  Future<Progress> getProgress(String questId);
  Future<Progress> start(String questId);
  Future<Progress> pause(String questId);
  Future<Progress> abandon(String questId);
  Future<StepCheckResult> checkStep(String questId, double lat, double lng);
}
