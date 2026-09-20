import 'package:dio/dio.dart';
import 'package:mobile/app/features/profile/domain/profile.dart';
import 'package:mobile/app/features/profile/domain/profile_repository.dart';
import 'package:mobile/app/features/profile/data/api/profile_api.dart';
import 'package:mobile/app/features/profile/data/dto/profile_dto.dart';
import 'package:mobile/app/core/storage/cache_manager.dart';

class ProfileRepositoryImpl implements ProfileRepository {
  final ProfileApi _api;

  ProfileRepositoryImpl(this._api);

  @override
  Future<Profile> getProfile() async {
    try {
      final profileResponse = await _api.getProfile();
      
      final historyResponse = await _api.getProfileWithHistory(profileResponse.username);
      final progressList = await _api.getProgressList();
      
      final Map<String, dynamic> mergedJson = {
        ...profileResponse.toJson(),
        'activeQuest': historyResponse['activeQuest'],
        'pausedQuests': historyResponse['pausedQuests'] ?? [],
        'completedQuests': historyResponse['completedQuests'] ?? [],
      };

      var profile = ProfileDto.fromJson(mergedJson).toDomain();
      
      // Merge progress info
      final progressMap = <String, Map<String, dynamic>>{};
      for (final p in progressList) {
        if (p is Map<String, dynamic>) {
          progressMap[p['questId'] as String] = p;
        }
      }

      QuestHistoryItem? mapProgress(QuestHistoryItem? item) {
        if (item == null) return null;
        final p = progressMap[item.questId];
        if (p != null) {
          return item.copyWith(
            currentStepNumber: p['currentStepNumber'] as int?,
            totalSteps: p['totalSteps'] as int?,
          );
        }
        return item;
      }

      profile = Profile(
        id: profile.id,
        username: profile.username,
        email: profile.email,
        createdAt: profile.createdAt,
        activeQuest: mapProgress(profile.activeQuest),
        pausedQuests: profile.pausedQuests.map((q) => mapProgress(q)!).toList(),
        completedQuests: profile.completedQuests.map((q) => mapProgress(q)!).toList(),
      );

      await CacheManager.setProfile(mergedJson);
      return profile;
    } on DioException catch (e) {
      final cached = CacheManager.getProfile();
      if (cached != null) {
        return ProfileDto.fromJson(cached).toDomain();
      }
      throw Exception(e.message ?? 'Ошибка загрузки профиля');
    }
  }

  @override
  Future<Profile> updateEmail(String email) async {
    try {
      final profile = await _api.updateEmail(email);
      await CacheManager.setProfile(profile.toJson());
      return profile.toDomain();
    } on DioException catch (e) {
      if (e.response?.statusCode == 422) {
        throw Exception('Неверный формат email или email уже занят');
      }
      throw Exception(e.message ?? 'Ошибка обновления');
    }
  }
}
