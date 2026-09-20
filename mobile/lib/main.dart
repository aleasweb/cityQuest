import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile/app/core/design/app_theme.dart';
import 'package:mobile/app/core/router/app_router.dart';
import 'package:mobile/app/core/storage/cache_manager.dart';
import 'package:mobile/app/core/api/api_client.dart';
import 'package:mobile/app/features/auth/application/auth_controller.dart';
import 'package:mobile/app/features/auth/data/api/auth_api.dart';
import 'package:mobile/app/features/auth/data/repository/auth_repository_impl.dart';
import 'package:mobile/app/features/cities/application/city_controller.dart';
import 'package:mobile/app/features/cities/data/api/city_api.dart';
import 'package:mobile/app/features/cities/data/repository/city_repository_impl.dart';
import 'package:mobile/app/features/profile/application/profile_controller.dart';
import 'package:mobile/app/features/profile/data/api/profile_api.dart';
import 'package:mobile/app/features/profile/data/repository/profile_repository_impl.dart';
import 'package:mobile/app/features/progress/application/progress_controller.dart';
import 'package:mobile/app/features/progress/data/api/progress_api.dart';
import 'package:mobile/app/features/progress/data/repository/progress_repository_impl.dart';
import 'package:mobile/app/features/quests/application/quest_controller.dart';
import 'package:mobile/app/features/quests/data/api/quest_api.dart';
import 'package:mobile/app/features/quests/data/repository/quest_repository_impl.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  await CacheManager.init();

  final apiClient = await ApiClient.create();

  runApp(
    ProviderScope(
      overrides: [
        authRepositoryProvider.overrideWithValue(AuthRepositoryImpl(AuthApi(apiClient))),
        cityRepositoryProvider.overrideWithValue(CityRepositoryImpl(CityApi(apiClient))),
        profileRepositoryProvider.overrideWithValue(ProfileRepositoryImpl(ProfileApi(apiClient))),
        progressRepositoryProvider.overrideWithValue(ProgressRepositoryImpl(ProgressApi(apiClient))),
        questRepositoryProvider.overrideWithValue(QuestRepositoryImpl(QuestApi(apiClient))),
      ],
      child: const CityQuestApp(),
    ),
  );
}

class CityQuestApp extends ConsumerWidget {
  const CityQuestApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);

    return MaterialApp.router(
      title: 'CityQuest',
      theme: AppTheme.light,
      darkTheme: AppTheme.dark,
      routerConfig: router,
    );
  }
}
