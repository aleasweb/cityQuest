import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/app/features/auth/application/auth_controller.dart';
import 'package:mobile/app/features/auth/presentation/screens/login_screen.dart';
import 'package:mobile/app/features/auth/presentation/screens/register_screen.dart';
import 'package:mobile/app/features/quests/presentation/screens/home_screen.dart';
import 'package:mobile/app/features/quests/presentation/screens/quest_detail_screen.dart';
import 'package:mobile/app/features/quests/presentation/screens/nearby/nearby_quests_screen.dart';
import 'package:mobile/app/features/progress/presentation/screens/active_quest_screen.dart';
import 'package:mobile/app/features/profile/presentation/screens/profile_screen.dart';

class AppRoutes {
  static const splash = '/';
  static const login = '/login';
  static const register = '/register';
  static const home = '/home';
  static const nearby = '/nearby';
  static const questDetail = '/quest/:id';
  static const activeQuest = '/quest/:id/active';
  static const profile = '/profile';
}

final routerProvider = Provider<GoRouter>((ref) {
  final refreshNotifier = ValueNotifier<int>(0);

  ref.listen(authControllerProvider, (_, __) {
    refreshNotifier.value++;
  });

  return GoRouter(
    initialLocation: AppRoutes.splash,
    refreshListenable: refreshNotifier,
    redirect: (context, state) {
      final authState = ref.read(authControllerProvider);
      final isGoingToLogin = state.matchedLocation == AppRoutes.login;
      final isGoingToRegister = state.matchedLocation == AppRoutes.register;

      if (authState.isLoading) return null;
      if (authState.hasError) return null;

      final isAuth = authState.value != null;

      if (!isAuth && !isGoingToLogin && !isGoingToRegister) {
        return AppRoutes.login;
      }

      if (isAuth && (isGoingToLogin || isGoingToRegister || state.matchedLocation == AppRoutes.splash)) {
        return AppRoutes.home;
      }

      return null;
    },
    routes: [
      GoRoute(
        path: AppRoutes.splash,
        builder: (context, state) => const _SplashScreen(),
      ),
      GoRoute(
        path: AppRoutes.login,
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: AppRoutes.register,
        builder: (context, state) => const RegisterScreen(),
      ),
      GoRoute(
        path: AppRoutes.home,
        builder: (context, state) => const HomeScreen(),
      ),
      GoRoute(
        path: AppRoutes.nearby,
        builder: (context, state) => const NearbyQuestsScreen(),
      ),
      GoRoute(
        path: AppRoutes.questDetail,
        builder: (context, state) => QuestDetailScreen(questId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: AppRoutes.activeQuest,
        builder: (context, state) => ActiveQuestScreen(questId: state.pathParameters['id']!),
      ),
      GoRoute(
        path: AppRoutes.profile,
        builder: (context, state) => const ProfileScreen(),
      ),
    ],
  );
});

class _SplashScreen extends StatelessWidget {
  const _SplashScreen();

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: Center(child: CircularProgressIndicator()),
    );
  }
}
