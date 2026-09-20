import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile/app/core/design/app_colors.dart';
import 'package:mobile/app/core/design/app_spacing.dart';
import 'package:mobile/app/shared/widgets/app_top_bar.dart';
import 'package:mobile/app/shared/widgets/app_filters_row.dart';
import 'package:mobile/app/shared/widgets/quest_card.dart';
import 'package:mobile/app/shared/widgets/skeleton.dart';
import 'package:mobile/app/shared/widgets/error_state.dart';
import 'package:mobile/app/features/cities/presentation/widgets/city_selector_sheet.dart';
import 'package:mobile/app/features/cities/application/city_controller.dart';
import 'package:mobile/app/features/quests/application/quest_controller.dart';
import 'package:mobile/app/features/quests/domain/quest_difficulty.dart';
import 'package:mobile/app/core/router/app_router.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final questsState = ref.watch(questsListControllerProvider);
    final filters = ref.watch(questFiltersStateProvider);
    final citiesState = ref.watch(citiesControllerProvider);

    String cityLabel = 'Любой город';
    if (filters.city != null && citiesState.hasValue) {
      final selectedCity = citiesState.value!.where((c) => c.id == filters.city).firstOrNull;
      if (selectedCity != null) {
        cityLabel = selectedCity.name;
      }
    }

    return Scaffold(
      backgroundColor: AppColors.backgroundLight,
      appBar: const AppTopBar(),
      body: Column(
        children: [
          // Filters are pinned at the top
          Container(
            color: AppColors.backgroundLight,
            padding: const EdgeInsets.symmetric(vertical: AppSpacing.s16),
            child: AppFiltersRow(
              filters: [
                AppFilterChip(
                  label: cityLabel,
                  isSelected: filters.city != null,
                  onTap: () async {
                    final cityId = await CitySelectorSheet.show(context);
                    if (cityId != null) {
                      ref.read(questFiltersStateProvider.notifier).setCity(cityId.isEmpty ? null : cityId);
                      ref.read(questsListControllerProvider.notifier).refresh();
                    }
                  },
                ),
                AppFilterChip(
                  label: filters.difficulty?.label ?? 'Сложность',
                  isSelected: filters.difficulty != null,
                  onTap: () {
                    showModalBottomSheet(
                      context: context,
                      builder: (context) => SafeArea(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            ListTile(
                              title: const Text('Любая сложность'),
                              onTap: () {
                                ref.read(questFiltersStateProvider.notifier).setDifficulty(null);
                                ref.read(questsListControllerProvider.notifier).refresh();
                                Navigator.pop(context);
                              },
                            ),
                            ...QuestDifficulty.values.map(
                              (d) => ListTile(
                                title: Text(d.label, style: TextStyle(color: d.color)),
                                onTap: () {
                                  ref.read(questFiltersStateProvider.notifier).setDifficulty(d);
                                  ref.read(questsListControllerProvider.notifier).refresh();
                                  Navigator.pop(context);
                                },
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ],
            ),
          ),
          
          // Scrollable quest list
          Expanded(
            child: CustomScrollView(
              slivers: [
                // Quest List
                SliverPadding(
                    padding: const EdgeInsets.symmetric(horizontal: AppSpacing.s24),
                    sliver: questsState.when(
                      data: (quests) {
                        if (quests.isEmpty) {
                          return const SliverToBoxAdapter(
                            child: Center(child: Text('Нет квестов')),
                          );
                        }
                        return SliverList(
                          delegate: SliverChildBuilderDelegate(
                            (context, index) {
                              final quest = quests[index];
                              return Padding(
                                padding: const EdgeInsets.only(bottom: AppSpacing.s16),
                                child: QuestCard(
                                  quest: quest,
                                  onTap: () => context.push(AppRoutes.questDetail.replaceAll(':id', quest.id)),
                                ),
                              );
                            },
                            childCount: quests.length,
                          ),
                        );
                      },
                      loading: () => SliverList(
                        delegate: SliverChildBuilderDelegate(
                          (context, index) => Padding(
                            padding: const EdgeInsets.only(bottom: AppSpacing.s16),
                            child: const Skeleton(height: 120, width: double.infinity, borderRadius: AppSpacing.radiusMedium),
                          ),
                          childCount: 3,
                        ),
                      ),
                      error: (err, st) => SliverToBoxAdapter(
                        child: ErrorState(
                          title: 'Ошибка',
                          message: err.toString(),
                          onRetry: () => ref.read(questsListControllerProvider.notifier).refresh(),
                        ),
                      ),
                    ),
                  ),
                  
                  const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.s32)),
                ],
              ),
            ),
        ],
      ),
    );
  }
}
