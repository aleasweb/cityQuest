import 'package:dio/dio.dart';
import 'package:mobile/app/features/cities/domain/city.dart';
import 'package:mobile/app/features/cities/domain/city_repository.dart';
import 'package:mobile/app/features/cities/data/api/city_api.dart';
import 'package:mobile/app/core/storage/cache_manager.dart';

class CityRepositoryImpl implements CityRepository {
  final CityApi _api;

  CityRepositoryImpl(this._api);

  @override
  Future<List<City>> getCities() async {
    try {
      final cached = CacheManager.getCities();
      if (cached != null) {
        return cached.map((json) => City(
              id: json['id'] as String,
              name: json['name'] as String,
              slug: json['slug'] as String,
              lat: (json['lat'] as num).toDouble(),
              lng: (json['lng'] as num).toDouble(),
            )).toList();
      }

      final dtos = await _api.getCities();
      await CacheManager.setCities(dtos.map((e) => {
            'id': e.id,
            'name': e.name,
            'slug': e.id, // Имитируем slug, так как его нет в DTO
            'lat': 0.0,   // Заглушка, так как lat нет в DTO
            'lng': 0.0,   // Заглушка, так как lng нет в DTO
          }).toList());
      return dtos.map((e) => e.toDomain()).toList();
    } on DioException catch (e) {
      throw Exception(e.message ?? 'Ошибка загрузки городов');
    }
  }
}
