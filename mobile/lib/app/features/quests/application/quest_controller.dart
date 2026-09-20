import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:mobile/app/features/quests/domain/quest.dart';
import 'package:mobile/app/features/quests/domain/quest_difficulty.dart';
import 'package:mobile/app/features/quests/domain/quest_repository.dart';
import 'package:mobile/app/features/auth/application/auth_controller.dart';

part 'quest_controller.g.dart';

@riverpod
QuestRepository questRepository(Ref ref) {
  throw UnimplementedError('Should be overridden in main');
}

@riverpod
class QuestFiltersState extends _$QuestFiltersState {
  @override
  QuestFilters build() {
    return const QuestFilters();
  }

  void setCity(String? city) {
    state = state.copyWith(city: city);
  }

  void setDifficulty(QuestDifficulty? difficulty) {
    state = state.copyWith(difficulty: difficulty);
  }
}

@riverpod
class QuestsListController extends _$QuestsListController {
  @override
  FutureOr<List<Quest>> build() async {
    final filters = ref.watch(questFiltersStateProvider);
    return _fetchPage(1, filters);
  }

  Future<List<Quest>> _fetchPage(int page, QuestFilters filters) async {
    final repository = ref.read(questRepositoryProvider);
    final response = await repository.getQuests(
      city: filters.city,
      difficulty: filters.difficulty,
      isPopular: filters.isPopular,
      page: page,
    );
    return response.items;
  }

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() {
      final filters = ref.read(questFiltersStateProvider);
      return _fetchPage(1, filters);
    });
  }
}

@riverpod
class QuestDetailController extends _$QuestDetailController {
  @override
  FutureOr<Quest> build(String id) async {
    final repository = ref.read(questRepositoryProvider);
    return repository.getQuestById(id);
  }

  Future<void> toggleLike() async {
    final current = state.value;
    if (current == null) return;

    final authState = ref.read(authControllerProvider);
    if (!authState.hasValue || authState.value == null) {
      throw Exception('Необходима авторизация');
    }

    final repository = ref.read(questRepositoryProvider);

    final wasLiked = current.isLiked;
    final newCount = wasLiked ? current.likesCount - 1 : current.likesCount + 1;

    state = AsyncData(current.copyWith(
      isLiked: !wasLiked,
      likesCount: newCount,
    ));

    try {
      await repository.toggleLike(id);
    } catch (e) {
      state = AsyncData(current);
      rethrow;
    }
  }
}
