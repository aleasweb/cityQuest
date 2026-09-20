import 'package:dio/dio.dart';

class EnvelopeInterceptor extends Interceptor {
  @override
  void onResponse(Response response, ResponseInterceptorHandler handler) {
    if (response.data is Map) {
      final map = response.data as Map;
      if (map.containsKey('data')) {
        if (map.containsKey('meta')) {
          response.extra['meta'] = map['meta'];
        }
        response.data = map['data'];
      }
    }
    super.onResponse(response, handler);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    if (err.response != null) {
      final statusCode = err.response?.statusCode;
      String message = 'Произошла неизвестная ошибка';

      if (err.response?.data is Map<String, dynamic>) {
        final data = err.response?.data as Map<String, dynamic>;
        if (data.containsKey('message')) {
          message = data['message'] as String;
        }
      } else {
        switch (statusCode) {
          case 400:
            message = 'Неверный запрос';
          case 401:
            message = 'Необходимо авторизоваться';
          case 403:
            message = 'Доступ запрещен';
          case 404:
            message = 'Ресурс не найден';
          case 409:
            message = 'Конфликт состояний';
          case 422:
            message = 'Ошибка валидации данных';
          case 500:
            message = 'Внутренняя ошибка сервера';
        }
      }

      err = err.copyWith(message: message);
    } else if (err.type == DioExceptionType.connectionTimeout ||
        err.type == DioExceptionType.receiveTimeout) {
      err = err.copyWith(message: 'Превышено время ожидания сервера');
    } else if (err.type == DioExceptionType.connectionError) {
      err = err.copyWith(message: 'Отсутствует подключение к интернету');
    }

    super.onError(err, handler);
  }
}
