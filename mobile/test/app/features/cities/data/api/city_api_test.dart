import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/app/features/cities/data/api/city_api.dart';
import '../../../../helpers/test_api_client.dart';

void main() {
  group('CityApi Tests', () {
    test('getCities should return list of cities', () async {
      final citiesResponse = {
        "data": [
          {"key": "moscow", "name": "Москва"},
          {"key": "spb", "name": "Санкт-Петербург"}
        ],
        "meta": {
          "total": 2,
          "count": 2
        }
      };

      final apiClient = TestApiClient.create({
        '/api/cities': citiesResponse,
      });

      final cityApi = CityApi(apiClient);
      final result = await cityApi.getCities();

      expect(result.length, 2);
      expect(result[0].id, "moscow");
      expect(result[0].name, "Москва");
      expect(result[1].id, "spb");
    });
  });
}
