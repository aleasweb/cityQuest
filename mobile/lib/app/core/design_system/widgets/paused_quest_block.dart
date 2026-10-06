import 'package:flutter/material.dart';
import 'package:mobile/app/core/config/app_config.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_spacing.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';

class PausedQuestBlock extends StatelessWidget {
  final String questName;
  final int currentStep;
  final int totalSteps;
  final String? imageUrl;
  final VoidCallback? onContinue;
  final VoidCallback onAbandon;

  const PausedQuestBlock({
    super.key,
    required this.questName,
    required this.currentStep,
    required this.totalSteps,
    this.imageUrl,
    this.onContinue,
    required this.onAbandon,
  });

  @override
  Widget build(BuildContext context) {
    if (questName.isEmpty || totalSteps == 0) {
      return const SizedBox.shrink();
    }

    final progress = currentStep / totalSteps;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(AppSpacing.radiusLarge),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.2 : 0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(AppSpacing.s8),
      child: IntrinsicHeight(
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              flex: 3,
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: AppSpacing.s8,
                  vertical: AppSpacing.s4,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      questName,
                      style: AppTextStyles.h3,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: AppSpacing.s4),
                    Text(
                      'Этап $currentStep из $totalSteps',
                      style: AppTextStyles.bodySmall.copyWith(
                        color: Theme.of(context).textTheme.bodySmall?.color,
                      ),
                    ),
                    const SizedBox(height: AppSpacing.s8),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(AppSpacing.radiusPill),
                      child: LinearProgressIndicator(
                        value: progress,
                        minHeight: 6,
                        backgroundColor: isDark ? AppColors.borderDark : AppColors.borderLight,
                        color: AppColors.primary,
                      ),
                    ),
                    const SizedBox(height: AppSpacing.s12),
                    if (onContinue != null)
                      Row(
                        children: [
                          ElevatedButton(
                            onPressed: onContinue,
                            style: ElevatedButton.styleFrom(
                              minimumSize: const Size(0, 36),
                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                              padding: const EdgeInsets.symmetric(
                                horizontal: AppSpacing.s12,
                                vertical: 0,
                              ),
                            ),
                            child: const Text('Продолжить'),
                          ),
                          const SizedBox(width: AppSpacing.s8),
                          OutlinedButton(
                            onPressed: onAbandon,
                            style: OutlinedButton.styleFrom(
                              foregroundColor: AppColors.error,
                              side: const BorderSide(color: AppColors.error),
                              minimumSize: const Size(36, 36),
                              padding: EdgeInsets.zero,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(AppSpacing.radiusPill),
                              ),
                            ),
                            child: const Icon(Icons.delete_outline, size: 20),
                          ),
                        ],
                      )
                    else
                      OutlinedButton(
                        onPressed: onAbandon,
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.error,
                          side: const BorderSide(color: AppColors.error),
                          minimumSize: const Size(0, 36),
                          padding: const EdgeInsets.symmetric(
                            horizontal: AppSpacing.s16,
                            vertical: 0,
                          ),
                        ),
                        child: const Text('Отказаться'),
                      ),
                  ],
                ),
              ),
            ),
            const SizedBox(width: AppSpacing.s8),
            Expanded(
              flex: 2,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(AppSpacing.radiusMedium),
                child: imageUrl != null && imageUrl!.isNotEmpty
                    ? Builder(
                        builder: (context) {
                          final isExternal = imageUrl!.startsWith('http');
                          String url = imageUrl!;
                          if (!isExternal) {
                            final baseUrl = AppConfig.apiBaseUrl.endsWith('/')
                                ? AppConfig.apiBaseUrl.substring(0, AppConfig.apiBaseUrl.length - 1)
                                : AppConfig.apiBaseUrl;
                            final path = imageUrl!.startsWith('/') ? imageUrl! : '/$imageUrl';
                            url = '$baseUrl$path';
                          }
                          return Image.network(
                            url,
                            fit: BoxFit.cover,
                            headers: !isExternal && AppConfig.apiHostHeader.isNotEmpty
                                ? {'Host': AppConfig.apiHostHeader}
                                : null,
                            errorBuilder: (context, error, stackTrace) => _buildPlaceholder(isDark),
                          );
                        },
                      )
                    : _buildPlaceholder(isDark),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPlaceholder(bool isDark) {
    return Container(
      color: isDark ? AppColors.borderDark : AppColors.borderLight,
      child: Center(
        child: Icon(
          Icons.map,
          size: 40,
          color: isDark ? AppColors.textSecondaryDark : AppColors.textSecondaryLight,
        ),
      ),
    );
  }
}
