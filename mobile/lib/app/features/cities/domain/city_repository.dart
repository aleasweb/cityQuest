import 'package:mobile/app/features/cities/domain/city.dart';

abstract class CityRepository {
  Future<List<City>> getCities();
}
