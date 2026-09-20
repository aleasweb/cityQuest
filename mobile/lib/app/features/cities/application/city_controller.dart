import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:mobile/app/features/cities/domain/city.dart';
import 'package:mobile/app/features/cities/domain/city_repository.dart';

part 'city_controller.g.dart';

@riverpod
CityRepository cityRepository(Ref ref) {
  throw UnimplementedError('Should be overridden in main');
}

@riverpod
class CitiesController extends _$CitiesController {
  @override
  FutureOr<List<City>> build() async {
    final repository = ref.read(cityRepositoryProvider);
    return repository.getCities();
  }
}
