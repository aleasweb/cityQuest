import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_spacing.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';
import 'package:mobile/app/core/design_system/widgets/active_quest_block.dart';
import 'package:mobile/app/core/design_system/widgets/paused_quest_block.dart';
import 'package:mobile/app/core/router/app_router.dart';
import 'package:mobile/app/features/profile/application/profile_controller.dart';
import 'package:mobile/app/features/profile/domain/profile.dart';
import 'package:mobile/app/features/progress/application/progress_controller.dart';
import 'package:mobile/app/features/quest_process/application/quest_process_controller.dart';
import 'package:mobile/app/shared/widgets/app_top_bar.dart';
import 'package:mobile/app/shared/widgets/error_state.dart';
import 'package:mobile/app/shared/widgets/toast.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final profileState = ref.watch(profileControllerProvider);

    return Scaffold(
      backgroundColor: AppColors.backgroundLight,
      appBar: const AppTopBar(),
      body: profileState.when(
        data: (profile) => _ProfileContent(profile: profile),
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.primary)),
        error: (err, st) => ErrorState(
          title: 'Ошибка',
          message: err.toString(),
          onRetry: () => ref.refresh(profileControllerProvider),
        ),
      ),
    );
  }
}

class _ProfileContent extends ConsumerWidget {
  final Profile profile;

  const _ProfileContent({required this.profile});

  Future<void> _resumePausedQuest(BuildContext context, WidgetRef ref, String questId) async {
    try {
      final progress = await ref.read(progressRepositoryProvider).start(questId);
      if (progress.status != 'active' && progress.status != 'ACTIVE') {
        throw Exception('Не удалось возобновить квест');
      }

      ref.invalidate(progressListControllerProvider);
      ref.invalidate(questProcessControllerProvider(questId));
      ref.invalidate(profileControllerProvider);
      await ref.read(profileControllerProvider.future);

      if (!context.mounted) return;
      context.push(AppRoutes.questProcess.replaceAll(':id', questId));
    } catch (e) {
      ref.invalidate(profileControllerProvider);
      ref.invalidate(progressListControllerProvider);
      if (!context.mounted) return;
      Toast.show(context, e.toString(), isError: true);
    }
  }

  Future<void> _abandonPausedQuest(BuildContext context, WidgetRef ref, QuestHistoryItem quest) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Отказаться от квеста?'),
        content: Text('Прогресс «${quest.title}» будет удалён.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Отмена'),
          ),
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(true),
            style: TextButton.styleFrom(foregroundColor: AppColors.error),
            child: const Text('Отказаться'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      await ref.read(progressRepositoryProvider).abandon(quest.questId);
      ref.invalidate(profileControllerProvider);
      ref.invalidate(progressListControllerProvider);
    } catch (e) {
      if (!context.mounted) return;
      Toast.show(context, e.toString(), isError: true);
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final activeQuest = profile.activeQuest;
    final hasActive = activeQuest != null;

    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.s20, vertical: AppSpacing.s8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Center(
            child: Container(
              width: 88,
              height: 88,
              decoration: const BoxDecoration(
                color: AppColors.primary,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.person_outline, size: 48, color: Colors.white),
            ),
          ),
          const SizedBox(height: AppSpacing.s16),
          Center(
            child: Text(
              profile.username,
              style: AppTextStyles.h2.copyWith(color: AppColors.textLight),
            ),
          ),
          const SizedBox(height: AppSpacing.s4),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                profile.email,
                style: AppTextStyles.bodySmall.copyWith(color: AppColors.textSecondaryLight),
              ),
              const SizedBox(width: AppSpacing.s8),
              GestureDetector(
                onTap: () {},
                child: const Icon(Icons.edit_outlined, size: 16, color: AppColors.textSecondaryLight),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.s24),
          Row(
            children: [
              Expanded(child: _StatCard(count: profile.completedQuests.length.toString(), label: 'Пройдено')),
              const SizedBox(width: AppSpacing.s12),
              Expanded(child: _StatCard(count: (hasActive ? 1 : 0).toString(), label: 'В процессе')),
              const SizedBox(width: AppSpacing.s12),
              Expanded(child: _StatCard(count: profile.pausedQuests.length.toString(), label: 'На паузе')),
            ],
          ),
          const SizedBox(height: AppSpacing.s32),

          // Active quest section — always visible
          Text('Активный квест', style: AppTextStyles.h3.copyWith(color: AppColors.textLight)),
          const SizedBox(height: AppSpacing.s16),
          if (hasActive)
            ActiveQuestBlock(
              questName: activeQuest.title,
              currentStep: activeQuest.currentStepNumber ?? 1,
              totalSteps: (activeQuest.totalSteps != null && activeQuest.totalSteps! > 0)
                  ? activeQuest.totalSteps!
                  : 1,
              imageUrl: activeQuest.imageUrl,
              onTap: () => context.push(
                AppRoutes.questProcess.replaceAll(':id', activeQuest.questId),
              ),
            )
          else
            Text(
              'Нет активных квестов',
              style: AppTextStyles.body.copyWith(color: AppColors.textSecondaryLight),
            ),
          const SizedBox(height: AppSpacing.s32),

          // Paused quests
          if (profile.pausedQuests.isNotEmpty) ...[
            Text('Квесты на паузе', style: AppTextStyles.h3.copyWith(color: AppColors.textLight)),
            const SizedBox(height: AppSpacing.s16),
            ...profile.pausedQuests.map(
              (q) => Padding(
                padding: const EdgeInsets.only(bottom: AppSpacing.s12),
                child: PausedQuestBlock(
                  questName: q.title,
                  currentStep: q.currentStepNumber ?? 1,
                  totalSteps: (q.totalSteps != null && q.totalSteps! > 0) ? q.totalSteps! : 1,
                  imageUrl: q.imageUrl,
                  onContinue: hasActive
                      ? null
                      : () => _resumePausedQuest(context, ref, q.questId),
                  onAbandon: () => _abandonPausedQuest(context, ref, q),
                ),
              ),
            ),
            const SizedBox(height: AppSpacing.s32),
          ],

          Container(
            decoration: BoxDecoration(
              color: AppColors.surfaceLight,
              borderRadius: BorderRadius.circular(AppSpacing.radiusLarge),
            ),
            padding: const EdgeInsets.symmetric(vertical: AppSpacing.s8),
            child: Column(
              children: [
                _MenuTile(title: 'Настройки', onTap: () {}),
                const Divider(
                  height: 1,
                  color: AppColors.borderLight,
                  indent: AppSpacing.s16,
                  endIndent: AppSpacing.s16,
                ),
                _MenuTile(title: 'Помощь и поддержка', onTap: () {}),
                const Divider(
                  height: 1,
                  color: AppColors.borderLight,
                  indent: AppSpacing.s16,
                  endIndent: AppSpacing.s16,
                ),
                _MenuTile(
                  title: 'Выйти',
                  textColor: AppColors.error,
                  showChevron: false,
                  onTap: () {
                    ref.read(profileControllerProvider.notifier).logout();
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.s40),
        ],
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String count;
  final String label;

  const _StatCard({required this.count, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: AppSpacing.s16, horizontal: AppSpacing.s8),
      decoration: BoxDecoration(
        color: AppColors.surfaceLight,
        borderRadius: BorderRadius.circular(AppSpacing.radiusMedium),
      ),
      child: Column(
        children: [
          Text(
            count,
            style: AppTextStyles.h2.copyWith(color: AppColors.textLight),
          ),
          const SizedBox(height: AppSpacing.s4),
          Text(
            label,
            style: AppTextStyles.bodySmall.copyWith(
              color: AppColors.textSecondaryLight,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

class _MenuTile extends StatelessWidget {
  final String title;
  final VoidCallback onTap;
  final Color? textColor;
  final bool showChevron;

  const _MenuTile({
    required this.title,
    required this.onTap,
    this.textColor,
    this.showChevron = true,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: AppSpacing.s16, horizontal: AppSpacing.s16),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              title,
              style: AppTextStyles.bodyLarge.copyWith(
                color: textColor ?? AppColors.textLight,
              ),
            ),
            if (showChevron)
              const Icon(Icons.chevron_right, color: AppColors.textSecondaryLight),
          ],
        ),
      ),
    );
  }
}
