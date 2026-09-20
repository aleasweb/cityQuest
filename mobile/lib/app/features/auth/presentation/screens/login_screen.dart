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

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  bool _showEmailForm = false;
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _onLogin() async {
    if (!_formKey.currentState!.validate()) return;

    final username = _usernameController.text.trim();
    final password = _passwordController.text;

    try {
      await ref.read(authControllerProvider.notifier).login(username, password);
      if (!mounted) return;

      final authState = ref.read(authControllerProvider);
      if (authState.hasError) {
        Toast.show(context, authState.error.toString(), isError: true);
      }
    } catch (e) {
      if (mounted) Toast.show(context, e.toString(), isError: true);
    }
  }

  Widget _buildAuthSelection() {
    return SingleChildScrollView(
      child: Column(
        children: [
          const SizedBox(height: 20),
          // Logo + brand name
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Image.asset('assets/images/logo.png', height: 48),
              const SizedBox(width: AppSpacing.s8),
              Text(
                'CityQuest',
                style: AppTextStyles.h1.copyWith(
                  color: AppColors.primary,
                  fontSize: 30,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          // City illustration
          Image.asset(
            'assets/images/city.png',
            width: double.infinity,
            fit: BoxFit.fitWidth,
          ),
          const SizedBox(height: 24),
          // Title
          Text('Твои городские квесты', style: AppTextStyles.h1.copyWith(color: AppColors.premiumTeal), textAlign: TextAlign.center),
          const SizedBox(height: 12),
          // Subtitle
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 40),
            child: Text(
              'Находите места,\nпрогуливаясь по городу',
              style: AppTextStyles.bodyLarge.copyWith(color: AppColors.textSecondaryLight),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 32),
          // Buttons
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.s24),
            child: ElevatedButton(
              onPressed: () => setState(() => _showEmailForm = true),
              child: const Text('Войти'),
            ),
          ),
          const SizedBox(height: 32),
          // Login text
          GestureDetector(
            onTap: () => context.push('/register'),
            child: RichText(
              textAlign: TextAlign.center,
              text: TextSpan(
                style: AppTextStyles.body.copyWith(color: AppColors.textSecondaryLight),
                children: [
                  const TextSpan(text: 'Нет аккаунта? '),
                  TextSpan(
                    text: 'Зарегистрироваться',
                    style: AppTextStyles.body.copyWith(
                      color: AppColors.primary,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 40),
        ],
      ),
    );
  }

  Widget _buildEmailForm() {
    final authState = ref.watch(authControllerProvider);
    final isLoading = authState.isLoading;

    return SingleChildScrollView(
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
                  onPressed: () => setState(() => _showEmailForm = false),
                ),
                Expanded(
                  child: Text(
                    'Вход по email',
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
              controller: _passwordController,
              labelText: 'Пароль',
              isPassword: true,
              validator: (v) => v!.isEmpty ? 'Обязательное поле' : null,
              enabled: !isLoading,
            ),
            const SizedBox(height: 32),
            AppButton(
              text: 'Войти',
              onPressed: _onLogin,
              isLoading: isLoading,
            ),
            const SizedBox(height: 32),
            GestureDetector(
              onTap: isLoading ? null : () => context.push('/register'),
              child: RichText(
                textAlign: TextAlign.center,
                text: TextSpan(
                  style: AppTextStyles.body.copyWith(color: AppColors.textSecondaryLight),
                  children: [
                    const TextSpan(text: 'Нет аккаунта? '),
                    TextSpan(
                      text: 'Зарегистрироваться',
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
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.centerLeft,
            end: Alignment.centerRight,
            colors: [
              AppColors.backgroundWarmEdge,
              AppColors.backgroundWarm,
              AppColors.backgroundWarm,
              AppColors.backgroundWarmEdge,
            ],
            stops: [0.0, 0.052, 0.948, 1.0],
          ),
        ),
        child: SafeArea(
          child: _showEmailForm ? _buildEmailForm() : _buildAuthSelection(),
        ),
      ),
    );
  }
}
