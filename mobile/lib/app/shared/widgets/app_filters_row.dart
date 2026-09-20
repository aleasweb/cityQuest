import 'package:flutter/material.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_spacing.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';

class AppFilterChip extends StatelessWidget {
  final String label;
  final IconData? icon;
  final bool isSelected;
  final VoidCallback onTap;

  const AppFilterChip({
    super.key,
    required this.label,
    this.icon,
    this.isSelected = false,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final textColor = isSelected ? AppColors.primary : AppColors.textLight;
    final bgColor = isSelected ? AppColors.primaryLight : AppColors.surfaceLight;
    final borderColor = isSelected ? AppColors.primaryLight : AppColors.borderLight;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(
          horizontal: AppSpacing.s16,
          vertical: AppSpacing.s8,
        ),
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(AppSpacing.radiusPill),
          border: Border.all(color: borderColor),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (icon != null) ...[
              Icon(icon, size: 16, color: textColor),
              const SizedBox(width: AppSpacing.s4),
            ],
            Text(
              label,
              style: AppTextStyles.body.copyWith(
                color: textColor,
                fontWeight: isSelected ? FontWeight.w500 : FontWeight.w400,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class AppFiltersRow extends StatelessWidget {
  final List<Widget> filters;

  const AppFiltersRow({
    super.key,
    required this.filters,
  });

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.s24),
      child: Row(
        children: filters.asMap().entries.map((entry) {
          return Padding(
            padding: EdgeInsets.only(
              right: entry.key != filters.length - 1 ? AppSpacing.s8 : 0,
            ),
            child: entry.value,
          );
        }).toList(),
      ),
    );
  }
}
