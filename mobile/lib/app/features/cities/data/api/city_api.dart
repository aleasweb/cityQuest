import 'package:mobile/app/core/api/api_client.dart';
import 'package:mobile/app/features/cities/data/dto/city_dto.dart';

class CityApi {
  final ApiClient _apiClient;

  CityApi(this._apiClient);

  Future<List<CityDto>> getCities() async {
    final response = await _apiClient.client.get('/api/cities');
    final data = response.data as List<dynamic>;
    return data.map((json) => CityDto.fromJson(json as Map<String, dynamic>)).toList();
  }
}
