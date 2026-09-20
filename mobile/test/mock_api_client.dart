import 'package:dio/dio.dart';
import 'package:mocktail/mocktail.dart';
import 'package:mobile/app/core/api/api_client.dart';

class MockApiClient extends Mock implements ApiClient {}
class MockDio extends Mock implements Dio {}
