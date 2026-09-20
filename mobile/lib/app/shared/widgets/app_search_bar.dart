import 'package:flutter/material.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_spacing.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';

class AppSearchBar extends StatelessWidget {
  final String hintText;
  final ValueChanged<String>? onChanged;

  const AppSearchBar({
    super.key,
    this.hintText = 'Город, район или название',
    this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 48,
      decoration: BoxDecoration(
        color: AppColors.surfaceLight,
        borderRadius: BorderRadius.circular(AppSpacing.radiusPill),
        border: Border.all(color: AppColors.borderLight),
      ),
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.s16),
      child: Row(
        children: [
          const Icon(
            Icons.search,
            color: AppColors.textSecondaryLight,
            size: 24,
          ),
          const SizedBox(width: AppSpacing.s8),
          Expanded(
            child: TextField(
              onChanged: onChanged,
              style: AppTextStyles.body.copyWith(color: AppColors.textLight),
              decoration: InputDecoration(
                hintText: hintText,
                hintStyle: AppTextStyles.body.copyWith(color: AppColors.textSecondaryLight),
                border: InputBorder.none,
                isDense: true,
                contentPadding: EdgeInsets.zero,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
