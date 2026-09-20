import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/app/features/auth/application/auth_controller.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_spacing.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';
import 'package:mobile/app/shared/widgets/toast.dart';
import 'package:mobile/app/shared/widgets/app_button.dart';
import 'package:mobile/app/shared/widgets/app_text_field.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  @override
  void dispose() {
    _usernameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _onRegister() async {
    if (!_formKey.currentState!.validate()) return;

    final username = _usernameController.text.trim();
    final email = _emailController.text.trim();
    final password = _passwordController.text;

    try {
      await ref.read(authControllerProvider.notifier).register(username, email, password);
      if (!mounted) return;

      final authState = ref.read(authControllerProvider);
      if (authState.hasError) {
        Toast.show(context, authState.error.toString(), isError: true);
      }
    } catch (e) {
      if (mounted) Toast.show(context, e.toString(), isError: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authControllerProvider);
    final isLoading = authState.isLoading;

    return Scaffold(
      backgroundColor: AppColors.backgroundLight,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.s24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.arrow_back),
                      onPressed: () => context.pop(),
                    ),
                    Expanded(
                      child: Text(
                        'Регистрация',
                        style: AppTextStyles.h2,
                        textAlign: TextAlign.center,
                      ),
                    ),
                    const SizedBox(width: 48), // Balance for centering
                  ],
                ),
                const SizedBox(height: 32),
                AppTextField(
                  controller: _usernameController,
                  labelText: 'Имя пользователя',
                  validator: (v) => v!.isEmpty ? 'Обязательное поле' : null,
                  enabled: !isLoading,
                ),
                const SizedBox(height: 16),
                AppTextField(
                  controller: _emailController,
                  labelText: 'Email',
                  keyboardType: TextInputType.emailAddress,
                  validator: (v) {
                    if (v == null || v.isEmpty) return 'Обязательное поле';
                    if (!v.contains('@')) return 'Неверный формат email';
                    return null;
                  },
                  enabled: !isLoading,
                ),
                const SizedBox(height: 16),
                AppTextField(
                  controller: _passwordController,
                  labelText: 'Пароль',
                  isPassword: true,
                  validator: (v) {
                    if (v == null || v.isEmpty) return 'Обязательное поле';
                    if (v.length < 6) return 'Пароль должен быть не менее 6 символов';
                    return null;
                  },
                  enabled: !isLoading,
                ),
                const SizedBox(height: 32),
                AppButton(
                  text: 'Зарегистрироваться',
                  onPressed: _onRegister,
                  isLoading: isLoading,
                ),
                const SizedBox(height: 32),
                GestureDetector(
                  onTap: isLoading ? null : () => context.pop(),
                  child: RichText(
                    textAlign: TextAlign.center,
                    text: TextSpan(
                      style: AppTextStyles.body.copyWith(color: AppColors.textSecondaryLight),
                      children: [
                        const TextSpan(text: 'Уже есть аккаунт? '),
                        TextSpan(
                          text: 'Войти',
                          style: AppTextStyles.body.copyWith(
                            color: AppColors.primary,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
