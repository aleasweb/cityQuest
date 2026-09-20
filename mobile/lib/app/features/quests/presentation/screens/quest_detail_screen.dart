import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/app/core/config/app_config.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';
import 'package:mobile/app/shared/widgets/app_button.dart';
import 'package:mobile/app/shared/widgets/app_top_bar.dart';
import 'package:mobile/app/shared/widgets/error_state.dart';
import 'package:mobile/app/core/router/app_router.dart';
import 'package:mobile/app/features/quests/application/quest_controller.dart';
import 'package:mobile/app/features/quests/domain/quest_difficulty.dart';
import 'package:mobile/app/features/auth/application/auth_controller.dart';
import 'package:mobile/app/shared/widgets/toast.dart';

class QuestDetailScreen extends ConsumerWidget {
  final String questId;

  const QuestDetailScreen({super.key, required this.questId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final questState = ref.watch(questDetailControllerProvider(questId));
    final authState = ref.watch(authControllerProvider);
    final isAuth = authState.value != null;
    final theme = Theme.of(context);

    return Scaffold(
      appBar: const AppTopBar(),
      body: questState.when(
        data: (quest) {
          return SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: Container(
                    height: 200,
                    width: double.infinity,
                    color: theme.colorScheme.outlineVariant,
                    child: quest.imageUrl != null && quest.imageUrl!.isNotEmpty
                        ? Builder(builder: (context) {
                            final isExternal = quest.imageUrl!.startsWith('http');
                            String url = quest.imageUrl!;
                            if (!isExternal) {
                              final baseUrl = AppConfig.apiBaseUrl.endsWith('/') 
                                  ? AppConfig.apiBaseUrl.substring(0, AppConfig.apiBaseUrl.length - 1) 
                                  : AppConfig.apiBaseUrl;
                              final path = quest.imageUrl!.startsWith('/') 
                                  ? quest.imageUrl! 
                                  : '/${quest.imageUrl}';
                              url = '$baseUrl$path';
                            }
                            return Image.network(
                              url,
                              fit: BoxFit.cover,
                              headers: !isExternal && AppConfig.apiHostHeader.isNotEmpty
                                  ? {'Host': AppConfig.apiHostHeader}
                                  : null,
                              errorBuilder: (context, error, stackTrace) => Center(
                                child: Icon(Icons.map, size: 64, color: theme.colorScheme.onSurfaceVariant),
                              ),
                            );
                          })
                        : Center(
                            child: Icon(Icons.map, size: 64, color: theme.colorScheme.onSurfaceVariant),
                          ),
                  ),
                ),
                const SizedBox(height: 24),
                Text(quest.title, style: AppTextStyles.h1),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(quest.city, style: AppTextStyles.body.copyWith(color: AppColors.primary)),
                    Text('От: ${quest.authorName}', style: AppTextStyles.bodySmall),
                  ],
                ),
                const SizedBox(height: 24),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    _InfoItem(icon: Icons.directions_walk, label: '${quest.distanceKm} км'),
                    _InfoItem(icon: Icons.timer, label: '${quest.durationMinutes} мин'),
                    _InfoItem(icon: Icons.speed, label: quest.difficulty.label),
                  ],
                ),
                const SizedBox(height: 32),
                Text('Описание', style: AppTextStyles.h2),
                const SizedBox(height: 8),
                Text(quest.description, style: AppTextStyles.body),
                const SizedBox(height: 40),
                AppButton(
                  text: 'Начать квест',
                  onPressed: () {
                    if (!isAuth) {
                      Toast.show(context, 'Необходимо войти в аккаунт');
                      return;
                    }

                    showDialog(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: const Text('Начать квест?'),
                        content: const Text('Вы готовы отправиться в приключение?'),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(ctx),
                            child: const Text('Отмена'),
                          ),
                          TextButton(
                            onPressed: () {
                              Navigator.pop(ctx);
                              context.push(AppRoutes.activeQuest.replaceAll(':id', questId));
                            },
                            child: const Text('Начать'),
                          ),
                        ],
                      ),
                    );
                  },
                ),
                const SizedBox(height: 40),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, st) => ErrorState(
          title: 'Ошибка',
          message: err.toString(),
          onRetry: () => ref.refresh(questDetailControllerProvider(questId).future),
        ),
      ),
    );
  }
}

class _InfoItem extends StatelessWidget {
  final IconData icon;
  final String label;

  const _InfoItem({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, color: Theme.of(context).colorScheme.onSurfaceVariant),
        const SizedBox(height: 4),
        Text(label, style: AppTextStyles.bodySmall),
      ],
    );
  }
}
