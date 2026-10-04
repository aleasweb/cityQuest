import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/app/core/config/app_config.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_spacing.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';
import 'package:mobile/app/features/quest_process/application/quest_process_controller.dart';
import 'package:mobile/app/features/quest_process/domain/quest_step.dart';

import 'package:mobile/app/shared/widgets/app_top_bar.dart';

class QuestProcessScreen extends ConsumerWidget {
  final String questId;

  const QuestProcessScreen({super.key, required this.questId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final stateAsync = ref.watch(questProcessControllerProvider(questId));

    return Scaffold(
      backgroundColor: AppColors.backgroundLight,
      appBar: const AppTopBar(),
      body: stateAsync.when(
        data: (state) {
          if (state.quest == null) {
            return const Center(child: Text('Квест не найден'));
          }

          final currentStep = state.steps.firstWhere(
            (s) => s.stepNumber == state.currentStepNumber,
            orElse: () => const QuestStep(
              stepNumber: 0,
              title: 'Загрузка...',
              description: '',
              targetLat: 0,
              targetLng: 0,
              radiusMeters: 0,
            ),
          );

          return SafeArea(
            child: Column(
              children: [
                // — Header —
                Padding(
                  padding: const EdgeInsets.only(
                    top: AppSpacing.s12,
                    bottom: AppSpacing.s8,
                  ),
                  child: Column(
                    children: [
                      Text(
                        state.quest!.title,
                        style: AppTextStyles.h2,
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: AppSpacing.s4),
                      Text(
                        'Этап ${state.currentStepNumber} из ${state.totalSteps}',
                        style: AppTextStyles.bodySmall.copyWith(
                          color: AppColors.textSecondaryLight,
                        ),
                      ),
                    ],
                  ),
                ),

                // — Scrollable content —
                Expanded(
                  child: ListView(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppSpacing.s16,
                    ),
                    children: [
                      _StepCard(step: currentStep, distanceToTarget: state.distanceToTarget),
                      const SizedBox(height: AppSpacing.s16),

                      // Error / success messages
                      if (state.locationCheckError != null) ...[
                        _MessageBanner(
                          message: state.locationCheckError!,
                          isError: true,
                        ),
                        const SizedBox(height: AppSpacing.s12),
                      ],
                      if (state.locationCheckSuccessMessage != null) ...[
                        _MessageBanner(
                          message: state.locationCheckSuccessMessage!,
                          isError: false,
                        ),
                        const SizedBox(height: AppSpacing.s12),
                      ],

                      // Primary action
                      if (state.isCompleted)
                        ElevatedButton(
                          onPressed: () => context.go('/quest/$questId'),
                          child: const Text('Квест завершён! Вернуться'),
                        )
                      else
                        ElevatedButton(
                          onPressed: state.isCheckingLocation
                              ? null
                              : () => ref
                                  .read(questProcessControllerProvider(questId).notifier)
                                  .checkLocation(),
                          child: state.isCheckingLocation
                              ? const SizedBox(
                                  width: 22,
                                  height: 22,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2.5,
                                    color: Colors.white,
                                  ),
                                )
                              : const Text('Я на месте'),
                        ),

                      const SizedBox(height: AppSpacing.s16),

                      // Info tiles
                      if (state.distanceToTarget != null)
                        _InfoTile(
                          icon: Icons.route_outlined,
                          text: 'Маршрут — до точки ${state.distanceToTarget!.round()} м',
                        ),
                      _InfoTile(
                        icon: Icons.list_alt_rounded,
                        text: 'Этапы маршрута — Пройдено '
                            '${state.currentStepNumber - 1} из ${state.totalSteps}',
                      ),
                      const SizedBox(height: AppSpacing.s24),
                    ],
                  ),
                ),

                // — Bottom pause bar —
                if (!state.isCompleted)
                  _PauseBar(
                    onTap: () async {
                      await ref
                          .read(questProcessControllerProvider(questId).notifier)
                          .pauseQuest();
                      if (context.mounted) context.go('/quest/$questId');
                    },
                  ),
              ],
            ),
          );
        },
        loading: () => const Center(
          child: CircularProgressIndicator(color: AppColors.primary),
        ),
        error: (error, _) => Center(
          child: Padding(
            padding: const EdgeInsets.all(AppSpacing.s32),
            child: Text('Ошибка: $error', textAlign: TextAlign.center),
          ),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────
// Current-step card (matches design/quest-step.png)
// ─────────────────────────────────────────────

class _StepCard extends StatelessWidget {
  final QuestStep step;
  final double? distanceToTarget;

  const _StepCard({required this.step, this.distanceToTarget});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surfaceLight,
        borderRadius: BorderRadius.circular(AppSpacing.radiusMedium),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(AppSpacing.s20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Media-type badge
          if (step.mediaType != null && step.mediaType!.isNotEmpty) ...[
            _MediaBadge(type: step.mediaType!),
            const SizedBox(height: AppSpacing.s12),
          ],

          // Title
          Text(step.title, style: AppTextStyles.h2),
          const SizedBox(height: AppSpacing.s8),

          // Description
          Text(
            step.description,
            style: AppTextStyles.body.copyWith(
              color: AppColors.textSecondaryLight,
              height: 1.6,
            ),
          ),

          // Image
          if (step.mediaUrl != null && step.mediaUrl!.isNotEmpty) ...[
            const SizedBox(height: AppSpacing.s16),
            ClipRRect(
              borderRadius: BorderRadius.circular(AppSpacing.radiusSmall),
              child: Image.network(
                _buildImageUrl(step.mediaUrl!),
                width: double.infinity,
                height: 200,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => Container(
                  width: double.infinity,
                  height: 200,
                  decoration: BoxDecoration(
                    color: AppColors.backgroundLight,
                    borderRadius: BorderRadius.circular(AppSpacing.radiusSmall),
                  ),
                  child: const Icon(
                    Icons.image_not_supported_outlined,
                    size: 40,
                    color: AppColors.textSecondaryLight,
                  ),
                ),
              ),
            ),
          ],

          // Distance indicator
          if (distanceToTarget != null) ...[
            const SizedBox(height: AppSpacing.s16),
            Row(
              children: [
                Container(
                  width: 8,
                  height: 8,
                  decoration: const BoxDecoration(
                    color: AppColors.success,
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: AppSpacing.s8),
                Expanded(
                  child: Text(
                    'До точки',
                    style: AppTextStyles.bodySmall.copyWith(
                      color: AppColors.textSecondaryLight,
                    ),
                  ),
                ),
                Text(
                  '${distanceToTarget!.round()} м',
                  style: AppTextStyles.bodySmall.copyWith(
                    color: AppColors.textSecondaryLight,
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  String _buildImageUrl(String url) {
    if (url.startsWith('http')) return url;
    final baseUrl = AppConfig.apiBaseUrl.endsWith('/')
        ? AppConfig.apiBaseUrl.substring(0, AppConfig.apiBaseUrl.length - 1)
        : AppConfig.apiBaseUrl;
    final path = url.startsWith('/') ? url : '/$url';
    return '$baseUrl$path';
  }
}

// ─────────────────────────────────────────────
// Media-type badge  (e.g. "ФОТО")
// ─────────────────────────────────────────────

class _MediaBadge extends StatelessWidget {
  final String type;
  const _MediaBadge({required this.type});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: AppSpacing.s12,
        vertical: AppSpacing.s4,
      ),
      decoration: BoxDecoration(
        color: AppColors.primaryLight,
        borderRadius: BorderRadius.circular(AppSpacing.radiusSmall),
      ),
      child: Text(
        type.toUpperCase(),
        style: AppTextStyles.bodySmall.copyWith(
          color: AppColors.primary,
          fontWeight: FontWeight.w600,
          letterSpacing: 0.5,
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────
// Error / success banner
// ─────────────────────────────────────────────

class _MessageBanner extends StatelessWidget {
  final String message;
  final bool isError;

  const _MessageBanner({required this.message, required this.isError});

  @override
  Widget build(BuildContext context) {
    final color = isError ? AppColors.error : AppColors.success;
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(
        horizontal: AppSpacing.s16,
        vertical: AppSpacing.s12,
      ),
      decoration: BoxDecoration(
        color: color.withOpacity(0.08),
        borderRadius: BorderRadius.circular(AppSpacing.radiusSmall),
      ),
      child: Text(
        message,
        style: AppTextStyles.body.copyWith(color: color),
        textAlign: TextAlign.center,
      ),
    );
  }
}

// ─────────────────────────────────────────────
// Info tile (route, steps progress)
// ─────────────────────────────────────────────

class _InfoTile extends StatelessWidget {
  final IconData icon;
  final String text;

  const _InfoTile({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: AppSpacing.s2),
      child: Container(
        padding: const EdgeInsets.symmetric(
          horizontal: AppSpacing.s16,
          vertical: AppSpacing.s16,
        ),
        decoration: BoxDecoration(
          color: AppColors.surfaceLight,
          borderRadius: BorderRadius.circular(AppSpacing.radiusSmall),
        ),
        child: Row(
          children: [
            Icon(icon, size: 20, color: AppColors.textSecondaryLight),
            const SizedBox(width: AppSpacing.s12),
            Expanded(
              child: Text(text, style: AppTextStyles.body),
            ),
            const Icon(
              Icons.chevron_right,
              size: 20,
              color: AppColors.textSecondaryLight,
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────
// Bottom pause bar
// ─────────────────────────────────────────────

class _PauseBar extends StatelessWidget {
  final VoidCallback onTap;

  const _PauseBar({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(AppSpacing.s16),
      child: ElevatedButton(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.surfaceLight,
          foregroundColor: AppColors.textLight,
          elevation: 2,
          shadowColor: Colors.black.withOpacity(0.1),
        ),
        onPressed: onTap,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.pause,
              size: 20,
            ),
            const SizedBox(width: AppSpacing.s8),
            const Text('Поставить на паузу'),
          ],
        ),
      ),
    );
  }
}
