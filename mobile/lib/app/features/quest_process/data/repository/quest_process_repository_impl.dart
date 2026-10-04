import 'package:dio/dio.dart';
import 'package:mobile/app/features/progress/domain/progress.dart';
import 'package:mobile/app/features/progress/data/dto/progress_dto.dart';
import 'package:mobile/app/features/quest_process/data/api/quest_process_api.dart';
import 'package:mobile/app/features/quest_process/domain/quest_process_repository.dart';
import 'package:mobile/app/features/quest_process/domain/quest_step.dart';

class QuestProcessRepositoryImpl implements QuestProcessRepository {
  final QuestProcessApi _api;

  QuestProcessRepositoryImpl(this._api);

  @override
  Future<StepCheckResult> checkStep(String questId, double lat, double lng) async {
    try {
      final dto = await _api.checkStep(questId, lat, lng);
      return dto.toDomain();
    } on DioException catch (e) {
      if (e.response?.statusCode == 422 && e.response?.data != null) {
        return StepCheckResponseDto.fromJson(e.response!.data as Map<String, dynamic>).toDomain();
      }
      throw _handleError(e);
    }
  }

  @override
  Future<QuestStep> getStep(String questId, int stepNumber) async {
    try {
      final dto = await _api.getStep(questId, stepNumber);
      return dto.toDomain();
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Exception _handleError(DioException e) {
    return Exception(e.message ?? 'Произошла ошибка');
  }
}
