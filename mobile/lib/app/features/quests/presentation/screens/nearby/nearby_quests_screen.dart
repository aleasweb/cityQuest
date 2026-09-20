import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';
import 'package:mobile/app/shared/widgets/quest_card.dart';
import 'package:mobile/app/core/router/app_router.dart';
import 'package:mobile/app/features/quests/application/quest_controller.dart';

final userLocationProvider = FutureProvider<Position>((ref) async {
  bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
  if (!serviceEnabled) {
    throw Exception('Службы геолокации отключены.');
  }

  LocationPermission permission = await Geolocator.checkPermission();
  if (permission == LocationPermission.denied) {
    permission = await Geolocator.requestPermission();
    if (permission == LocationPermission.denied) {
      throw Exception('Разрешения на геолокацию отклонены');
    }
  }

  if (permission == LocationPermission.deniedForever) {
    throw Exception('Разрешения на геолокацию отклонены навсегда. Измените в настройках ОС.');
  }

  return await Geolocator.getCurrentPosition();
});

class NearbyQuestsScreen extends ConsumerWidget {
  const NearbyQuestsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final locationState = ref.watch(userLocationProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Квесты рядом'),
      ),
      body: locationState.when(
        data: (position) {
          final questsState = ref.watch(questsListControllerProvider);

          return questsState.when(
            data: (quests) {
              if (quests.isEmpty) return const Center(child: Text('Поблизости ничего нет'));
              return ListView.builder(
                padding: const EdgeInsets.all(24),
                itemCount: quests.length,
                itemBuilder: (context, index) {
                  final q = quests[index];
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 16),
                    child: QuestCard(
                      quest: q,
                      onTap: () => context.push(AppRoutes.questDetail.replaceAll(':id', q.id)),
                    ),
                  );
                },
              );
            },
            loading: () => const Center(child: CircularProgressIndicator()),
            error: (err, st) => Center(child: Text(err.toString())),
          );
        },
        loading: () => const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              CircularProgressIndicator(),
              SizedBox(height: 16),
              Text('Определяем местоположение...'),
            ],
          ),
        ),
        error: (err, st) => Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.location_disabled, size: 48, color: AppColors.error),
                const SizedBox(height: 16),
                Text(err.toString(), textAlign: TextAlign.center, style: AppTextStyles.body),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: () async {
                    await Geolocator.openAppSettings();
                  },
                  child: const Text('Открыть настройки'),
                )
              ],
            ),
          ),
        ),
      ),
    );
  }
}
