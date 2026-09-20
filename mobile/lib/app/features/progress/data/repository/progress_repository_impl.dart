import 'package:dio/dio.dart';
import 'package:mobile/app/features/progress/domain/progress.dart';
import 'package:mobile/app/features/progress/domain/progress_repository.dart';
import 'package:mobile/app/features/progress/data/api/progress_api.dart';
import 'package:mobile/app/features/progress/data/dto/progress_dto.dart';

class ProgressRepositoryImpl implements ProgressRepository {
  final ProgressApi _api;

  ProgressRepositoryImpl(this._api);

  @override
  Future<List<Progress>> getList() async {
    try {
      final dtos = await _api.getList();
      return dtos.map((e) => e.toDomain()).toList();
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  @override
  Future<Progress> getProgress(String questId) async {
    try {
      final dto = await _api.getProgress(questId);
      return dto.toDomain();
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  @override
  Future<Progress> start(String questId) async {
    try {
      final dto = await _api.start(questId);
      return dto.toDomain();
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  @override
  Future<Progress> pause(String questId) async {
    try {
      final dto = await _api.pause(questId);
      return dto.toDomain();
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  @override
  Future<Progress> abandon(String questId) async {
    try {
      await _api.abandon(questId);
      return Progress(
        questId: questId,
        status: 'ABANDONED',
        currentStepNumber: 0,
        totalSteps: 0,
        startedAt: DateTime.now(),
      );
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

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

  Exception _handleError(DioException e) {
    if (e.response?.statusCode == 409) {
      return Exception('У вас уже есть активный квест. Завершите или отмените его.');
    }
    return Exception(e.message ?? 'Произошла ошибка');
  }
}
