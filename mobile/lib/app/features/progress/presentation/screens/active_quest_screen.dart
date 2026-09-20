import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';
import 'package:mobile/app/shared/widgets/app_button.dart';
import 'package:mobile/app/shared/widgets/toast.dart';
import 'package:mobile/app/features/progress/application/progress_controller.dart';
import 'package:mobile/app/features/quests/application/quest_controller.dart';

class ActiveQuestScreen extends ConsumerWidget {
  final String questId;

  const ActiveQuestScreen({super.key, required this.questId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final progressState = ref.watch(activeProgressControllerProvider(questId));
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Квест'),
        actions: [
          IconButton(
            icon: const Icon(Icons.more_vert),
            onPressed: () => _showMenu(context, ref, questId),
          ),
        ],
      ),
      body: progressState.when(
        data: (progress) {
          if (progress == null) {
            return const Center(child: Text('Прогресс не найден'));
          }

          if (progress.status == 'COMPLETED') {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.emoji_events, size: 64, color: AppColors.primary),
                  const SizedBox(height: 16),
                  Text('Квест завершен!', style: AppTextStyles.h1),
                ],
              ),
            );
          }

          return Stack(
            children: [
              FlutterMap(
                options: const MapOptions(
                  initialCenter: LatLng(55.751244, 37.618423),
                  initialZoom: 15.0,
                ),
                children: [
                  TileLayer(
                    urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    userAgentPackageName: 'com.cityquest.mobile',
                  ),
                  const SimpleAttributionWidget(
                    source: Text('OpenStreetMap contributors'),
                  ),
                ],
              ),
              Positioned(
                bottom: 0,
                left: 0,
                right: 0,
                child: Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: theme.colorScheme.surface,
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                    boxShadow: const [
                      BoxShadow(color: Colors.black12, blurRadius: 10, offset: Offset(0, -2))
                    ],
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text('Шаг ${progress.currentStepNumber} из ${progress.totalSteps}', style: AppTextStyles.h2),
                      const SizedBox(height: 8),
                      const Text('Двигайтесь к следующей точке'),
                      const SizedBox(height: 24),
                      AppButton(
                        text: 'Проверить позицию',
                        onPressed: () async {
                          try {
                            final res = await ref.read(activeProgressControllerProvider(questId).notifier).checkCurrentStep();
                            if (!context.mounted) return;

                            if (res.success) {
                              Toast.show(context, res.isQuestCompleted == true ? 'Квест пройден!' : 'Точка найдена!');
                            } else {
                              Toast.show(context, 'Слишком далеко: ${res.distance}м. Направление: ${res.bearing}°');
                            }
                          } catch (e) {
                            if (context.mounted) Toast.show(context, e.toString(), isError: true);
                          }
                        },
                      ),
                    ],
                  ),
                ),
              ),
            ],
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, st) => Center(child: Text(e.toString())),
      ),
    );
  }

  void _showMenu(BuildContext context, WidgetRef ref, String questId) {
    final theme = Theme.of(context);
    showModalBottomSheet(
      context: context,
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.pause),
              title: const Text('Поставить на паузу'),
              onTap: () {
                ref.read(activeProgressControllerProvider(questId).notifier).pause();
                Navigator.pop(ctx);
              },
            ),
            ListTile(
              leading: Icon(Icons.cancel, color: theme.colorScheme.error),
              title: Text('Прервать квест', style: TextStyle(color: theme.colorScheme.error)),
              onTap: () {
                ref.read(activeProgressControllerProvider(questId).notifier).abandon();
                Navigator.pop(ctx);
              },
            ),
          ],
        ),
      ),
    );
  }
}
