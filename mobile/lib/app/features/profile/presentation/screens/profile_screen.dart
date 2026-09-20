import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';
import 'package:mobile/app/shared/widgets/error_state.dart';
import 'package:mobile/app/shared/widgets/app_top_bar.dart';
import 'package:mobile/app/features/profile/application/profile_controller.dart';
import 'package:mobile/app/features/profile/domain/profile.dart';

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

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Avatar
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
          const SizedBox(height: 16),
          
          // Name and Email
          Center(
            child: Text(
              profile.username,
              style: AppTextStyles.h2.copyWith(color: AppColors.textLight),
            ),
          ),
          const SizedBox(height: 4),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                profile.email,
                style: AppTextStyles.bodySmall.copyWith(color: AppColors.textSecondaryLight),
              ),
              const SizedBox(width: 8),
              GestureDetector(
                onTap: () {
                  // Edit profile action
                },
                child: const Icon(Icons.edit_outlined, size: 16, color: AppColors.textSecondaryLight),
              ),
            ],
          ),
          const SizedBox(height: 24),

          // Stats
          Row(
            children: [
              Expanded(child: _StatCard(count: profile.completedQuests.length.toString(), label: 'Пройдено')),
              const SizedBox(width: 12),
              Expanded(child: _StatCard(count: (profile.activeQuest != null ? 1 : 0).toString(), label: 'В процессе')),
              const SizedBox(width: 12),
              const Expanded(child: _StatCard(count: '0', label: 'В избранном')),
            ],
          ),
          const SizedBox(height: 32),

          // Active Quest
          if (profile.activeQuest != null) ...[
            Text('Продолжить прохождение', style: AppTextStyles.h3.copyWith(color: AppColors.textLight)),
            const SizedBox(height: 16),
            _ActiveQuestCard(quest: profile.activeQuest!),
            const SizedBox(height: 32),
          ],

          // Paused Quests
          if (profile.pausedQuests.isNotEmpty) ...[
            Text('Квесты на паузе', style: AppTextStyles.h3.copyWith(color: AppColors.textLight)),
            const SizedBox(height: 16),
            ...profile.pausedQuests.map((q) => Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: _PausedQuestCard(quest: q),
            )),
            const SizedBox(height: 32),
          ],

          // Bottom Menu
          Container(
            decoration: BoxDecoration(
              color: AppColors.surfaceLight,
              borderRadius: BorderRadius.circular(20),
            ),
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: Column(
              children: [
                _MenuTile(
                  title: 'Настройки',
                  onTap: () {},
                ),
                const Divider(height: 1, color: AppColors.borderLight, indent: 16, endIndent: 16),
                _MenuTile(
                  title: 'Помощь и поддержка',
                  onTap: () {},
                ),
                const Divider(height: 1, color: AppColors.borderLight, indent: 16, endIndent: 16),
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
          const SizedBox(height: 40),
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
      padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
      decoration: BoxDecoration(
        color: AppColors.surfaceLight,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        children: [
          Text(
            count,
            style: AppTextStyles.h2.copyWith(color: AppColors.textLight),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: AppTextStyles.bodySmall.copyWith(
              color: AppColors.textSecondaryLight,
              fontSize: 12,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

class _ActiveQuestCard extends StatelessWidget {
  final QuestHistoryItem quest;

  const _ActiveQuestCard({required this.quest});

  @override
  Widget build(BuildContext context) {
    final stepText = (quest.currentStepNumber != null && quest.totalSteps != null)
        ? 'Этап ${quest.currentStepNumber} из ${quest.totalSteps}'
        : 'В процессе';

    final progress = (quest.currentStepNumber != null && quest.totalSteps != null && quest.totalSteps! > 0)
        ? (quest.currentStepNumber! / quest.totalSteps!)
        : 0.0;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceLight,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: quest.imageUrl != null
                ? Image.network(quest.imageUrl!, width: 100, height: 100, fit: BoxFit.cover)
                : Container(
                    width: 100,
                    height: 100,
                    color: AppColors.borderLight,
                    child: const Icon(Icons.image, color: AppColors.textSecondaryLight),
                  ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  quest.title,
                  style: AppTextStyles.h3.copyWith(color: AppColors.textLight),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 8),
                Text(
                  stepText,
                  style: AppTextStyles.bodySmall.copyWith(color: AppColors.textSecondaryLight),
                ),
                const SizedBox(height: 8),
                if (quest.currentStepNumber != null) ...[
                  ClipRRect(
                    borderRadius: BorderRadius.circular(4),
                    child: LinearProgressIndicator(
                      value: progress,
                      backgroundColor: AppColors.borderLight,
                      color: AppColors.primary,
                      minHeight: 6,
                    ),
                  ),
                  const SizedBox(height: 12),
                ],
                ElevatedButton(
                  onPressed: () {
                    context.push('/quest/${quest.questId}');
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
                    minimumSize: const Size(0, 36),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(20),
                    ),
                  ),
                  child: const Text('Продолжить', style: TextStyle(fontWeight: FontWeight.w600)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _PausedQuestCard extends StatelessWidget {
  final QuestHistoryItem quest;

  const _PausedQuestCard({required this.quest});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => context.push('/quest/${quest.questId}'),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: AppColors.surfaceLight,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: quest.imageUrl != null
                  ? Image.network(quest.imageUrl!, width: 72, height: 72, fit: BoxFit.cover)
                  : Container(
                      width: 72,
                      height: 72,
                      color: AppColors.borderLight,
                      child: const Icon(Icons.image, color: AppColors.textSecondaryLight),
                    ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    quest.title,
                    style: AppTextStyles.bodyLarge.copyWith(fontWeight: FontWeight.w600, color: AppColors.textLight),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    quest.city ?? 'Неизвестно',
                    style: AppTextStyles.bodySmall.copyWith(color: AppColors.textSecondaryLight),
                  ),
                ],
              ),
            ),
          ],
        ),
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
        padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
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
