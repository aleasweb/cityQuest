import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:mobile/app/core/api/envelope_interceptor.dart';

class MockResponseInterceptorHandler extends Mock implements ResponseInterceptorHandler {}

void main() {
  group('EnvelopeInterceptor', () {
    late EnvelopeInterceptor interceptor;

    setUp(() {
      interceptor = EnvelopeInterceptor();
    });

    test('should unwrap data and meta from successful response', () async {
      final response = Response<dynamic>(
        requestOptions: RequestOptions(path: '/test'),
        data: <String, dynamic>{
          'data': ['item1', 'item2'],
          'meta': {'total': 2}
        },
      );

      final handler = MockResponseInterceptorHandler();
      
      interceptor.onResponse(response, handler);

      // После onResponse response.data должен содержать только массив data
      expect(response.data, isA<List>());
      expect((response.data as List).length, 2);
      expect((response.data as List)[0], 'item1');
      
      // А meta должна быть в extra
      expect(response.extra['meta'], isNotNull);
      expect(response.extra['meta']['total'], 2);
    });

    test('should not modify response if data key is missing', () async {
      final response = Response<dynamic>(
        requestOptions: RequestOptions(path: '/test'),
        data: <String, dynamic>{
          'status': 'ok',
          'items': ['item1']
        },
      );

      final handler = MockResponseInterceptorHandler();
      interceptor.onResponse(response, handler);

      expect(response.data, isA<Map>());
      expect((response.data as Map)['status'], 'ok');
      expect(response.extra['meta'], isNull);
    });
  });
}
