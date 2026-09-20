import 'package:dio/dio.dart';
import 'package:mocktail/mocktail.dart';
import 'package:mobile/app/core/api/api_client.dart';
import 'package:mobile/app/core/api/envelope_interceptor.dart';

class MockHttpClientAdapter extends Mock implements HttpClientAdapter {}

class MockApiClient extends Mock implements ApiClient {
  final Dio mockDio;
  MockApiClient(this.mockDio);

  @override
  Dio get client => mockDio;

  @override
  Future<void> clearCookies() async {}
}

Dio createTestDio(HttpClientAdapter adapter) {
  final dio = Dio();
  dio.httpClientAdapter = adapter;
  dio.interceptors.add(EnvelopeInterceptor());
  return dio;
}

class FakeRequestOptions extends Fake implements RequestOptions {}
