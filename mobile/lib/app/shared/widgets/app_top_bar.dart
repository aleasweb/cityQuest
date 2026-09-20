import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_spacing.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';
import 'package:mobile/app/core/router/app_router.dart';
import 'package:mobile/app/features/auth/application/auth_controller.dart';

class AppTopBar extends ConsumerWidget implements PreferredSizeWidget {
  final List<Widget>? actions;

  const AppTopBar({super.key, this.actions});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authControllerProvider);

    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.symmetric(
          horizontal: AppSpacing.s24,
          vertical: AppSpacing.s12,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                if (Navigator.of(context).canPop())
                  Padding(
                    padding: const EdgeInsets.only(right: AppSpacing.s12),
                    child: IconButton(
                      icon: const Icon(Icons.arrow_back),
                      color: AppColors.primary,
                      onPressed: () => Navigator.of(context).pop(),
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(),
                    ),
                  ),
                Image.asset(
                  'assets/images/logo.png',
                  height: 36,
                  fit: BoxFit.contain,
                ),
              ],
            ),
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                if (actions != null) ...[
                  ...actions!,
                  const SizedBox(width: AppSpacing.s12),
                ],
                authState.maybeWhen(
                  data: (user) {
                if (user != null) {
                  return GestureDetector(
                    onTap: () => context.push(AppRoutes.profile),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          user.username,
                          style: AppTextStyles.h2.copyWith(
                            fontSize: 18,
                            color: AppColors.premiumTeal,
                          ),
                        ),
                        const SizedBox(width: AppSpacing.s12),
                        CircleAvatar(
                          radius: 20,
                          backgroundColor: AppColors.primaryLight,
                          child: Text(
                            user.username.isNotEmpty ? user.username[0].toUpperCase() : '?',
                            style: AppTextStyles.h3.copyWith(color: AppColors.primary),
                          ),
                        ),
                      ],
                    ),
                  );
                }
                return TextButton(
                  onPressed: () => context.push(AppRoutes.login),
                  style: TextButton.styleFrom(
                    foregroundColor: AppColors.primary,
                    textStyle: AppTextStyles.button,
                  ),
                  child: const Text('Войти'),
                );
              },
              orElse: () => const SizedBox(width: 40, height: 40),
            ),
          ],
        ),
      ],
    ),
  ),
);
  }

  @override
  Size get preferredSize => const Size.fromHeight(72);
}
