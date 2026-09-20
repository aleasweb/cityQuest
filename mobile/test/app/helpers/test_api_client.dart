import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:mobile/app/core/api/api_client.dart';
import 'package:mobile/app/core/api/envelope_interceptor.dart';

class MockHttpAdapter implements HttpClientAdapter {
  final Map<String, dynamic> mockResponses;

  MockHttpAdapter(this.mockResponses);

  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<List<int>>? requestStream,
    Future<void>? cancelFuture,
  ) async {
    final path = options.path;
    for (final key in mockResponses.keys) {
      if (path.contains(key)) {
        return ResponseBody.fromString(
          jsonEncode(mockResponses[key]),
          200,
          headers: {
            Headers.contentTypeHeader: ['application/json'],
          },
        );
      }
    }
    
    throw DioException(
      requestOptions: options,
      error: 'No mock response found for path: $path',
    );
  }

  @override
  void close({bool force = false}) {}
}

class TestApiClient extends ApiClient {
  TestApiClient._(Dio dio) : super.forTest(dio);

  static TestApiClient create(Map<String, dynamic> mockResponses) {
    final dio = Dio();
    
    dio.httpClientAdapter = MockHttpAdapter(mockResponses);
    dio.interceptors.add(EnvelopeInterceptor());
    
    return TestApiClient._(dio);
  }
}
