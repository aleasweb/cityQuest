import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:mobile/app/features/cities/domain/city.dart';

part 'city_dto.g.dart';
part 'city_dto.freezed.dart';

@freezed
abstract class CityDto with _$CityDto {
  const CityDto._();

  const factory CityDto({
    @JsonKey(name: 'key') required String id,
    required String name,
  }) = _CityDto;

  factory CityDto.fromJson(Map<String, dynamic> json) => _$CityDtoFromJson(json);

  City toDomain() => City(id: id, name: name, slug: id, lat: 0.0, lng: 0.0);
}
