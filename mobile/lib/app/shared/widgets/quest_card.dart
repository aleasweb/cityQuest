import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:mobile/app/core/config/app_config.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_spacing.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';
import 'package:mobile/app/features/quests/domain/quest.dart';
import 'package:mobile/app/features/quests/domain/quest_difficulty.dart';

class QuestCard extends StatelessWidget {
  final Quest quest;
  final VoidCallback onTap;

  const QuestCard({
    super.key,
    required this.quest,
    required this.onTap,
  });

  Widget _buildPlaceholder() {
    return Container(
      color: AppColors.borderLight,
      child: const Center(
        child: Icon(Icons.image, size: 32, color: AppColors.textSecondaryLight),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: 180,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(AppSpacing.radiusMedium),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withAlpha(13),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(AppSpacing.radiusMedium),
          child: Stack(
            children: [
              // Background Image
              Positioned.fill(
                child: quest.imageUrl != null && quest.imageUrl!.isNotEmpty
                    ? Builder(
                        builder: (context) {
                          final isExternal = quest.imageUrl!.startsWith('http');
                          
                          // Ensure we don't end up with double slashes or missing slashes
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
                            errorBuilder: (context, error, stackTrace) => _buildPlaceholder(),
                          );
                        }
                      )
                    : _buildPlaceholder(),
              ),
              
              // Glassmorphism Overlay (Bottom Aligned)
              Positioned(
                left: 0,
                right: 0,
                bottom: 0,
                child: ClipRect(
                  child: BackdropFilter(
                    filter: ImageFilter.blur(sigmaX: 8.0, sigmaY: 8.0),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: AppSpacing.s12,
                        vertical: AppSpacing.s8,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.black.withAlpha(120), // Dark tint for readability
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          // Title and Difficulty Dot
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Expanded(
                                child: Text(
                                  quest.title,
                                  style: AppTextStyles.h3.copyWith(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              const SizedBox(width: AppSpacing.s8),
                              Container(
                                margin: const EdgeInsets.only(top: 4.0), // Reduced top margin for better alignment
                                width: 10, // Made dot slightly smaller to match compact design
                                height: 10,
                                decoration: BoxDecoration(
                                  color: quest.difficulty.color,
                                  shape: BoxShape.circle,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: AppSpacing.s4), // Reduced spacing between rows
                          
                          // Meta info row (City, Likes, Duration)
                          Row(
                            children: [
                              const Icon(Icons.location_on_outlined, size: 14, color: AppColors.primary), // Made icon slightly smaller
                              const SizedBox(width: AppSpacing.s4),
                              Expanded(
                                child: Text(
                                  quest.city,
                                  style: AppTextStyles.bodySmall.copyWith(
                                    color: AppColors.primary,
                                    fontWeight: FontWeight.w500,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              
                              /* Likes
                              const Icon(Icons.favorite_border, size: 14, color: AppColors.primary),
                              const SizedBox(width: AppSpacing.s4),
                              Text(
                                '${quest.likesCount}',
                                style: AppTextStyles.bodySmall.copyWith(
                                  color: AppColors.primary,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              const SizedBox(width: AppSpacing.s12),
                              */
                              
                              // Duration
                              const Icon(Icons.access_time, size: 14, color: AppColors.primary), // Made icon slightly smaller
                              const SizedBox(width: AppSpacing.s4),
                              Text(
                                '${quest.durationMinutes}',
                                style: AppTextStyles.bodySmall.copyWith(
                                  color: AppColors.primary,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

